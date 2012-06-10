<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2012 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

// load base model
require_once (dirname(__FILE__) . DS . 'model.php');

class WFModelUpdates extends WFModel {

    var $url = 'https://www.joomlacontenteditor.net/index.php?option=com_updates&format=raw';

    public static function canUpdate() {
        if (!function_exists('curl_init')) {
            return function_exists('file_get_contents') && function_exists('ini_get') && ini_get('allow_url_fopen');
        }

        return true;
    }

    /**
     * Get extension versions
     * @return Array
     */
    function getVersions() {
        $db = JFactory::getDBO();

        $versions = array('joomla' => array(), 'jce' => array());

        // Get Component xml
        $com_xml = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce' . DS . 'jce.xml');
        // set component version
        $versions['joomla']['com_jce'] = $com_xml['version'];
        // get mediabox version
        $mediabox_xml_file = WF_JOOMLA15 ? JPATH_PLUGINS . DS . 'system' . DS . 'jcemediabox.xml' : JPATH_PLUGINS . DS . 'system' . DS . 'jcemediabox' . DS . 'jcemediabox.xml';
        // set mediabox version
        if (file_exists($mediabox_xml_file)) {
            $mediabox_xml = JApplicationHelper::parseXMLInstallFile($mediabox_xml_file);
            $versions['joomla']['plg_jcemediabox'] = $mediabox_xml['version'];
        }

        $model = JModel::getInstance('plugins', 'WFModel');

        // get all plugins
        $plugins = $model->getPlugins();
        // get all extensions
        $extensions = $model->getExtensions();

        foreach ($plugins as $plugin) {
            if ($plugin->core == 0) {

                $file = WF_EDITOR_PLUGINS . DS . $plugin->name . DS . $plugin->name . '.xml';

                $xml = JApplicationHelper::parseXMLInstallFile($file);
                $versions['jce']['jce_' . $plugin->name] = $xml['version'];
            }
        }

        foreach ($extensions as $extension) {
            if ($extension->core == 0) {

                $file = WF_EDITOR_EXTENSIONS . DS . $extension->folder . DS . $extension->extension . '.xml';

                $xml = JApplicationHelper::parseXMLInstallFile($file);
                $versions['jce']['jce_' . $extension->folder . '_' . $extension->extension] = $xml['version'];
            }
        }

        return $versions;
    }

    /**
     * Check for extension updates
     * @return String JSON string of updates
     */
    function check() {
        $result = false;

        // Get all extensions and version numbers
        $data = array('task' => 'check', 'jversion' => WF_JOOMLA15 ? '1.5' : '1.7');

        wfimport('admin.helpers.extension');

        $component = WFExtensionHelper::getComponent();
        $params = new WFParameter($component->params, '', 'preferences');

        // get update key
        $key = $params->get('updates_key', '');
        $type = $params->get('updates_type', '');

        // encode it
        if (!empty($key)) {
            $data['key'] = urlencode($key);
        }

        if ($type) {
            $data['type'] = $type;
        }

        $req = array();

        // create request data
        foreach ($this->getVersions() as $type => $extension) {
            foreach ($extension as $item => $value) {
                $data[$type . '[' . urlencode($item) . ']'] = urlencode($value);
            }
        }

        foreach ($data as $key => $value) {
            $req[] = $key . '=' . urlencode($value);
        }

        // connect
        $result = $this->connect($this->url, implode('&', $req));

        return $result;
    }

    /**
     * Download update
     * @return String JSON string
     */
    function download() {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $config = JFactory::getConfig();

        $result = array('error' => WFText::_('WF_UPDATES_DOWNLOAD_ERROR'));

        $id = JRequest::getInt('id');

        $file = $this->connect($this->url, 'task=download&id=' . $id);

        if ($file) {
            $data = json_decode($file);

            // get update file
            if ($data->name && $data->url && $data->hash) {
                $tmp = $config->getValue('config.tmp_path');
                // create path for package file
                $path = $tmp . DS . basename($data->name);
                // download file
                if ($this->connect($data->url, null, $path)) {
                    if (JFile::exists($path) && @filesize($path) > 0) {
                        // check hash and file type
                        if ($data->hash == md5(md5_file($path)) && preg_match('/\.(zip|tar|gz)$/', $path)) {
                            $result = array('file' => basename($path), 'hash' => $data->hash, 'installer' => $data->installer, 'type' => isset($data->type) ? $data->type : '');
                        } else {
                            // fail and delete file
                            $result = array('error' => WFText::_('WF_UPDATES_ERROR_FILE_VERIFICATION_FAIL'));
                            if (JFile::exists($path)) {
                                @JFile::delete($path);
                            }
                        }
                    } else {
                        $result = array('error' => WFText::_('WF_UPDATES_ERROR_FILE_MISSING_OR_INVALID'));
                    }
                } else {
                    $result = array('error' => WFText::_('WF_UPDATES_DOWNLOAD_ERROR_DATA_TRANSFER'));
                }
            } else {
                $result = array('error' => WFText::_('WF_UPDATES_DOWNLOAD_ERROR_MISSING_DATA'));
            }
        }

        return json_encode($result);
    }

    /**
     * Install extension update
     * @return String JSON string
     */
    function install() {
        jimport('joomla.installer.installer');
        jimport('joomla.installer.helper');
        jimport('joomla.filesystem.file');

        $config = JFactory::getConfig();
        $result = array('error' => WFText::_('WF_UPDATES_INSTALL_ERROR'));

        // get vars
        $file = JRequest::getCmd('file');
        $hash = JRequest::getVar('hash', '', 'POST', 'alnum');
        $method = JRequest::getWord('installer');
        $type = JRequest::getWord('type');

        // check for vars
        if ($file && $hash && $method) {
            $tmp = $config->getValue('config.tmp_path');
            $path = $tmp . DS . $file;
            // check if file exists
            if (JFile::exists($path)) {
                // check hash
                if ($hash == md5(md5_file($path))) {
                    if ($extract = JInstallerHelper::unpack($path)) {
                        // get new Installer instance
                        $installer = JInstaller::getInstance();

                        // set installer adapter
                        if ($method == 'jce') {
                            // create jce plugin adapter
                            $model = JModel::getInstance('installer', 'WFModel');
                            $installer->setAdapter($extract['type'], $model->getAdapter($extract['type']));
                        }

                        // install
                        if ($installer->install($extract['extractdir'])) {
                            // get destination path
                            $path = $installer->getPath('extension_root');
                            // get manifest
                            $manifest = basename($installer->getPath('manifest'));
                            // delete update manifest if any eg: _iframes_155_156.xml
                            if ($type == 'patch' && preg_match('/^_[0-9a-z_\.-]+\.xml$/', $manifest)) {
                                if (JFile::exists($path . DS . $manifest)) {
                                    @JFile::delete($path . DS . $manifest);
                                }
                            }
                            // installer message
                            $result = array('error' => '', 'text' => WFText::_($installer->get('message'), $installer->get('message')));
                        }
                        // cleanup package and extract dir
                        JInstallerHelper::cleanupInstall($extract['packagefile'], $extract['extractdir']);
                    } else {
                        $result = array('error' => WFText::_('WF_UPDATES_ERROR_FILE_EXTRACT_FAIL'));
                    }
                } else {
                    $result = array('error' => WFText::_('WF_UPDATES_ERROR_FILE_VERIFICATION_FAIL'));
                }
            } else {
                $result = array('error' => WFText::_('WF_UPDATES_ERROR_FILE_MISSING_OR_INVALID'));
            }
        }
        return json_encode($result);
    }

    /**
     * @copyright		Copyright (C) 2009 Ryan Demmer. All rights reserved.
     * @copyright 		Copyright (C) 2006-2010 Nicholas K. Dionysopoulos
     * @param 	String 	$url URL to resource
     * @param 	Array  	$data [optional] Array of key value pairs
     * @param 	String 	$download [optional] path to file to write to
     * @return 	Mixed 	Boolean or JSON String on error
     */
    function connect($url, $data = '', $download = '') {
        @error_reporting(E_ERROR);

        jimport('joomla.filesystem.file');

        $fp = false;

        $fopen = function_exists('file_get_contents') && function_exists('ini_get') && ini_get('allow_url_fopen');

        // try file_get_contents first (requires allow_url_fopen)
        if ($fopen) {
            if ($download) {
                // use Joomla! installer function
                jimport('joomla.installer.helper');
                return @JInstallerHelper::downloadPackage($url, $download);
            } else {
                $options = array('http' => array('method' => 'POST', 'timeout' => 10, 'content' => $data));

                $context = stream_context_create($options);
                return @file_get_contents($url, false, $context);
            }
            // Use curl if it exists
        } else if (function_exists('curl_init')) {

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // Pretend we are IE7, so that webservers play nice with us
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
            //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // The @ sign allows the next line to fail if open_basedir is set or if safe mode is enabled
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            @curl_setopt($ch, CURLOPT_MAXREDIRS, 20);

            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            if ($data && !$download) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            // file download
            if ($download) {
                $fp = @fopen($download, 'wb');
                @curl_setopt($ch, CURLOPT_FILE, $fp);
            }

            $result = curl_exec($ch);

            // file download
            if ($download && $result === false) {
                die(json_encode(array('error' => 'TRANSFER ERROR : ' . curl_error($ch))));
            }

            curl_close($ch);

            // close fopen handler
            if ($fp) {
                @fclose($fp);
            }

            return $result;

            // error - no update support
        } else {
            return array('error' => WFText::_('WF_UPDATES_DOWNLOAD_ERROR_NO_CONNECT'));
        }

        return array('error' => WFText::_('WF_UPDATES_DOWNLOAD_ERROR_NO_CONNECT'));
    }

    function log($msg) {
        jimport('joomla.error.log');
        $log = JLog::getInstance('updates.txt');
        $log->addEntry(array('comment' => 'LOG: ' . $msg));
    }

}

?>
