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
require_once (dirname(__FILE__) . '/model.php');

class WFModelPlugins extends WFModel {

    public function getCommands() {
        //$xml  = JFactory::getXMLParser('Simple');
        $file = dirname(__FILE__) . '/commands.xml';
        $xml = WFXMLElement::getXML($file);

        $commands = array();

        if ($xml) {
            //$elements = WFXMLHelper::getElements($xml, 'commands');

            foreach ($xml->children() as $command) {
                $name = (string) $command->name;

                if ($name) {
                    $commands[$name] = new StdClass();

                    foreach ($command->children() as $item) {
                        $key = $item->getName();
                        $value = (string) $item;
                        $commands[$name]->$key = $value;
                    }

                    $commands[$name]->type = 'command';
                }
            }
        }

        return $commands;
    }

    public function getPlugins() {
        jimport('joomla.filesystem.folder');

        $plugins = array();

        // get core xml
        $xml = WFXMLElement::getXML(dirname(__FILE__) . '/plugins.xml');

        if ($xml) {

            foreach ($xml->children() as $plugin) {
                $name = (string) $plugin->name;

                if ($name) {
                    $plugins[$name] = new StdClass();

                    foreach ($plugin->children() as $item) {
                        $key = $item->getName();
                        $value = (string) $item;

                        $plugins[$name]->$key = $value;
                    }

                    $plugins[$name]->type = 'plugin';

                    //$plugins[$name]->author = '';
                    //$plugins[$name]->version = '';
                    //$plugins[$name]->creationdate = '';
                    //$plugins[$name]->description = '';
                    
                    $plugins[$name]->path = str_replace(JPATH_SITE, '', WF_EDITOR_PLUGINS) . '/' . $name;
                }
            }
        }

        unset($xml);

        // get all Plugins
        $folders = JFolder::folders(WF_EDITOR_PLUGINS, '.', false, true, array_merge(array('.svn', 'CVS'), array_keys($plugins)));

        jimport('joomla.plugin.helper');
        $external = JPluginHelper::getPlugin('jce');

        // get external
        foreach ($external as $plugin) {
            $path = JPATH_PLUGINS . '/jce/' . $plugin->name;

            if (is_dir($path) && is_file($path . '/editor_plugin.js')) {
                $folders[] = $path;
            }
        }

        foreach ($folders as $folder) {
            $name = basename($folder);
            $file = $folder . '/' . $name . '.xml';

            if (is_file($file)) {
                $xml = WFXMLElement::getXML($folder . '/' . $name . '.xml');

                if ($xml) {
                    $params = $xml->params;

                    if (!isset($plugins[$name])) {
                        $plugins[$name] = new StdClass();

                        $plugins[$name]->name = $name;

                        $plugins[$name]->title = (string) $xml->name;
                        $plugins[$name]->icon = (string) $xml->icon;

                        $editable = (int) $xml->attributes()->editable;
                        $plugins[$name]->editable = $editable ? $editable : ($params && count($params->children()) ? 1 : 0);

                        $row = (int) $xml->attributes()->row;

                        $plugins[$name]->row = $row ? $row : 4;
                        $plugins[$name]->core = (int) $xml->attributes()->core ? 1 : 0;
                    }
                    // relative path
                    $plugins[$name]->path = str_replace(JPATH_SITE, '', $folder);

                    $plugins[$name]->author = (string) $xml->author;
                    $plugins[$name]->version = (string) $xml->version;
                    $plugins[$name]->creationdate = (string) $xml->creationDate;
                    $plugins[$name]->description = (string) $xml->description;

                    $plugins[$name]->authorUrl = (string) $xml->authorUrl;
                    $plugins[$name]->type = 'plugin';
                }
            }
        }

        return $plugins;
    }

    /**
     * Get a plugin's extensions
     * @param object $plugin
     * @return
     */
    public function getExtensions() {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $extensions = array();

        // recursively get all extension files
        $files = JFolder::files(WF_EDITOR_EXTENSIONS, '\.xml$', true, true);

        foreach ($files as $file) {
            $object = new StdClass();
            $object->folder = basename(dirname($file));
            $object->manifest = $file;
            $object->plugins = array();
            $name = basename($file, '.xml');
            $object->name = $name;
            $object->description = '';
            $object->id = $object->folder . '.' . $object->name;

            $xml = WFXMLElement::getXML($file);

            if ($xml) {
                $plugins = (string) $xml->plugins;

                if ($plugins) {
                    $object->plugins = explode(',', $plugins);
                }

                $object->name = (string) $xml->name;
                $object->title = (string) $xml->name;
                $object->description = (string) $xml->description;

                $object->creationdate = (string) $xml->creationDate;
                $object->author = (string) $xml->author;
                $object->version = (string) $xml->version;
                $object->type = (string) $xml->attributes()->folder;
                $object->authorUrl = (string) $xml->authorUrl;


                $object->folder = (string) $xml->attributes()->folder;
                $object->core = (int) $xml->attributes()->core ? 1 : 0;

                if ($object->core == 0) {
                    // load language
                    $language = JFactory::getLanguage();
                    $language->load('com_jce_' . $object->folder . '_' . $name, JPATH_SITE);
                }
            }
            $object->extension = $name;
            $extensions[] = $object;
        }

        return $extensions;
    }

    /**
     * Process import data from XML file
     * @param object $file XML file
     * @param boolean $install Can be used by the package installer
     * @return
     */
    public function processImport($file, $install = false) {
        return true;
    }

    /**
     * Enable the installed plugin and add it to the editor toolbar
     * @param object $plugin Plugin object
     * @return boolean
     */
    public static function installPostflight($name, $installer) {
        $db = JFactory::getDBO();
        
        jimport('joomla.filesystem.folder');

        $plugin = JTable::getInstance('extension');
        // find the plugin
        $id = $plugin->find(array('type' => 'plugin', 'folder' => 'jce', 'element' => $name));

        // load the plugin and enable
        if ($id) {
            $plugin->load($id);
            
            // plugin is installed
            if ($plugin->extension_id) {
                $plugin->publish(1);
                
                $legacy = JPATH_SITE . '/components/com_jce/editor/tiny_mce/plugins/' . $name;
                
                // remove old version
                if (JFolder::exists($legacy)) {
                    @JFolder::delete($legacy);
                }

                // get manifest from installer
                $xml = $installer->manifest;

                if ($xml) {
                    $plugin->row = (string) $xml->attributes()->row;
                    $plugin->icon = (string) $xml->icon;

                    // Install plugin install default profile layout if a row is set
                    if (is_numeric($plugin->row) && (int) $plugin->row) {
                        JTable::addIncludePath(dirname(dirname(__FILE__)) . '/tables');
                        // Add to Default Group
                        $profile = JTable::getInstance('profiles', 'WFTable');

                        $query = 'SELECT id'
                                . ' FROM #__wf_profiles'
                                . ' WHERE name = '
                                . $db->Quote('Default');
                        $db->setQuery($query);
                        $id = $db->loadResult();

                        $profile->load($id);
                        // Add to plugins list
                        $plugins = explode(',', $profile->plugins);

                        if (!in_array($plugin->element, $plugins)) {
                            $plugins[] = $plugin->element;
                        }

                        $profile->plugins = implode(',', $plugins);

                        if ($plugin->icon) {
                            if (!in_array($plugin->element, preg_split('/[;,]+/', $profile->rows))) {
                                // get rows as array	
                                $rows = explode(';', $profile->rows);
                                // get key (row number)
                                $key = (int) $plugin->row - 1;
                                // get row contents as array
                                $row = explode(',', $rows[$key]);
                                // add plugin name to end of row
                                $row[] = $plugin->element;
                                // add row data back to rows array
                                $rows[$key] = implode(',', $row);

                                $profile->rows = implode(';', $rows);
                            }
                        }

                        if (!$profile->store()) {
                            return false;
                        }
                    }
                }
            }
        }

        return false;
    }

}