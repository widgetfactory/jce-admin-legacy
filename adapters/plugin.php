<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die('RESTRICTED');

/**
 * JCE Plugin installer
 *
 * @package   JCE
 * @subpackage  Installer
 * @since   1.5
 */
class WFInstallerPlugin extends JObject {

    /**
     * Constructor
     *
     * @param object  $parent Parent object [JInstaller instance]
     * @return  void
     */
    function __construct(&$parent) {
        $this->parent = $parent;
    }

    /**
     * Setup manifest data
     * @param object $manifest
     */
    private function setManifest($manifest) {
        // element
        foreach (array(
    'name',
    'version',
    'description',
    'installfile',
    'uninstallfile',
    'icon'
        ) as $item) {
            $this->set($item, WFXMLHelper::getElement($manifest, $item));
        }

        // attribute
        foreach (array(
    'group',
    'type',
    'plugin',
    'core',
    'editable',
    'row'
        ) as $item) {
            $this->set($item, WFXMLHelper::getAttribute($manifest, $item));
        }

        // elements
        foreach (array(
    'files',
    'languages',
    'media'
        ) as $item) {
            $this->set($item, WFXMLHelper::getElements($manifest, $item));
        }

        return true;
    }

    /**
     * Install method
     *
     * @access  public
     * @return  boolean True on success
     */
    public function install() {
        // Get a database connector object
        $db = $this->parent->getDBO();

        // Get the extension manifest object
        $manifest = $this->parent->getManifest();
        // setup manifest data
        $this->setManifest($manifest);

        $this->parent->set('name', $this->get('name'));
        $this->parent->set('version', $this->get('version'));
        $this->parent->set('message', $this->get('description'));

        $plugin = $this->get('plugin');
        $group = $this->get('group');
        $type = $this->get('type');

        // JCE Plugin
        if (!empty($plugin)) {
            if (version_compare($this->version, '2.0.0', '<')) {
                $this->parent->abort(WFText::_('WF_INSTALLER_INCORRECT_VERSION'));
                return false;
            }

            $this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'tiny_mce' . DS . 'plugins' . DS . $plugin);
        } else {
            // Non-JCE plugin type, probably JCE MediaBox or JCE Editor
            if ($type == 'plugin' && ($group == 'system' || $group == 'editors')) {
                require_once(JPATH_LIBRARIES . DS . 'joomla' . DS . 'installer' . DS . 'adapters' . DS . 'plugin.php');

                $adapter = new JInstallerPlugin($this->parent, $db);
                $this->parent->setAdapter('plugin', $adapter);
                return $adapter->install();
            } else {
                $this->parent->abort(WFText::_('WF_INSTALLER_EXTENSION_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_NO_PLUGIN_FILE'));
                return false;
            }
        }

        /**
         * ---------------------------------------------------------------------------------------------
         * Filesystem Processing Section
         * ---------------------------------------------------------------------------------------------
         */
        // If the extension directory does not exist, lets create it
        $created = false;
        if (!file_exists($this->parent->getPath('extension_root'))) {
            if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
                $this->parent->abort(WFText::_('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_MKDIR_ERROR') . ' : "' . $this->parent->getPath('extension_root') . '"');
                return false;
            }
        }

        // Set overwrite flag if not set by Manifest
        $this->parent->setOverwrite(true);

        /*
         * If we created the extension directory and will want to remove it if we
         * have to roll back the installation, lets add it to the installation
         * step stack
         */
        if ($created) {
            $this->parent->pushStep(array(
                'type' => 'folder',
                'path' => $this->parent->getPath('extension_root')
            ));
        }

        // Copy all necessary files
        if (!$this->parent->parseFiles($this->get('files'), -1)) {
            // Install failed, roll back changes
            $this->parent->abort();
            return false;
        }
        // install languages
        $this->parent->parseLanguages($this->get('languages'), 0);
        // install media
        $this->parent->parseMedia($this->get('media'), 0);

        // Load the language file
        $language = JFactory::getLanguage();
        $language->load('com_jce_' . trim($plugin), JPATH_SITE);

        $install = $this->get('install.script');

        if ($install) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root') . DS . $install)) {
                $path['src'] = $this->parent->getPath('source') . DS . $install;
                $path['dest'] = $this->parent->getPath('extension_root') . DS . $install;
                if (!$this->parent->copyFiles(array(
                            $path
                        ))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_PHP_INSTALL_FILE_ERROR'));
                    return false;
                }
            }
        }

        $uninstall = $this->get('uninstall.script');

        if ($uninstall) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root') . DS . $uninstall)) {
                $path['src'] = $this->parent->getPath('source') . DS . $uninstall;
                $path['dest'] = $this->parent->getPath('extension_root') . DS . $uninstall;
                if (!$this->parent->copyFiles(array(
                            $path
                        ))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_PHP_UNINSTALL_FILE_ERROR'));
                    return false;
                }
            }
        }

        // Install plugin install default profile layout if a row is set
        if (is_numeric($this->get('row')) && intval($this->get('row'))) {
            // Add to Default Group
            $profile = JTable::getInstance('profiles', 'WFTable');

            $query = 'SELECT id' . ' FROM #__wf_profiles' . ' WHERE name = ' . $db->Quote('Default');
            $db->setQuery($query);
            $id = $db->loadResult();

            $profile->load($id);
            // Add to plugins list
            $plugins = explode(',', $profile->plugins);

            if (!in_array($this->get('plugin'), $plugins)) {
                $plugins[] = $this->get('plugin');
            }

            $profile->plugins = implode(',', $plugins);

            if ($this->get('icon')) {
                if (!in_array($this->get('plugin'), preg_split('/[;,]+/', $profile->rows))) {
                    // get rows as array	
                    $rows = explode(';', $profile->rows);
                    // get key (row number)
                    $key = intval($this->get('row')) - 1;
                    // get row contents as array
                    $row = explode(',', $rows[$key]);
                    // add plugin name to end of row
                    $row[] = $this->get('plugin');
                    // add row data back to rows array
                    $rows[$key] = implode(',', $row);

                    $profile->rows = implode(';', $rows);
                }
            }

            if (!$profile->store()) {
                JError::raiseWarning(100, 'WF_INSTALLER_PLUGIN_PROFILE_ERROR');
            }
        }
        /**
         * ---------------------------------------------------------------------------------------------
         * Finalization and Cleanup Section
         * ---------------------------------------------------------------------------------------------
         */
        // Lastly, we will copy the manifest file to its appropriate place.
        if (!$this->parent->copyManifest(-1)) {
            // Install failed, rollback changes
            $this->parent->abort(WFText::_('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_SETUP_COPY_ERROR'));
            return false;
        }

        /*
         * If we have an install script, lets include it, execute the custom
         * install method, and append the return value from the custom install
         * method to the installation message.
         */
        $install = $this->get('install.script');

        if ($install) {
            if (file_exists($this->parent->getPath('extension_root') . DS . $install)) {
                ob_start();
                ob_implicit_flush(false);
                require_once($this->parent->getPath('extension_root') . DS . $install);
                if (function_exists('jce_install')) {
                    if (jce_install() === false) {
                        $this->parent->abort(WFText::_('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_CUSTOM_INSTALL_ERROR'));
                        return false;
                    }
                } else if (function_exists('com_install')) {
                    if (com_install() === false) {
                        $this->parent->abort(WFText::_('WF_INSTALLER_PLUGIN_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_CUSTOM_INSTALL_ERROR'));
                        return false;
                    }
                }
                $msg = ob_get_contents();
                ob_end_clean();
                if ($msg != '') {
                    $this->parent->set('extension.message', $msg);
                }
            }
        } else {
            $this->parent->set('extension.message', '');
        }

        // post-install

        $this->addIndexfiles();

        return true;
    }

    /**
     * Add index.html files to each folder
     * @access private
     */
    private function addIndexfiles() {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        // get the base file
        $file = WF_ADMINISTRATOR . DS . 'index.html';
        $path = $this->parent->getPath('extension_root');

        if (is_file($file) && is_dir($path)) {

            JFile::copy($file, $path . DS . basename($file));

            // admin component
            $folders = JFolder::folders($path, '.', true, true);

            foreach ($folders as $folder) {
                JFile::copy($file, $folder . DS . basename($file));
            }
        }
    }

    /**
     * Uninstall method
     *
     * @access  public
     * @param 	string   $name  The name of the plugin to uninstall
     * @return  boolean True on success
     */
    public function uninstall($name) {
        // Initialize variables
        $row = null;
        $retval = true;
        $db = $this->parent->getDBO();

        $this->parent->set('name', $name);

        // Set the plugin root path
        $this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'tiny_mce' . DS . 'plugins' . DS . $name);

        $manifest = $this->parent->getPath('extension_root') . DS . $name . '.xml';

        // Load the language file
        $language = JFactory::getLanguage();
        $language->load('com_jce_' . trim($name), JPATH_SITE);

        if (file_exists($manifest)) {
            $xml = WFXMLHelper::getXML($manifest);

            if (!$this->setManifest($xml)) {
                JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_MANIFEST_INVALID'));
            }

            $this->parent->set('name', $this->get('name'));
            $this->parent->set('version', $this->get('version'));
            $this->parent->set('message', $this->get('description'));

            // can't remove a core plugin
            if ($this->get('core') == 1) {
                JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . JText::sprintf('WF_INSTALLER_WARNCOREPLUGIN', WFText::_($this->get('name'))));
                return false;
            }

            // Remove all media and languages as well
            $this->parent->removeFiles($this->get('languages'), 0);
            $this->parent->removeFiles($this->get('media'), 0);

            /**
             * ---------------------------------------------------------------------------------------------
             * Custom Uninstallation Script Section
             * ---------------------------------------------------------------------------------------------
             */
            // Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
            $uninstall = $this->get('uninstall.script');

            if ($uninstall) {
                // Element exists, does the file exist?
                if (is_file($this->parent->getPath('extension_root') . DS . $uninstall)) {
                    ob_start();
                    ob_implicit_flush(false);
                    require_once($this->parent->getPath('extension_root') . DS . $uninstall);
                    if (function_exists('com_uninstall')) {
                        if (com_uninstall() === false) {
                            JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_CUSTOM_UNINSTALL_ERROR'));
                            $retval = false;
                        }
                    }
                    $msg = ob_get_contents();
                    ob_end_clean();
                    if ($msg != '') {
                        $this->parent->set('extension.message', $msg);
                    }
                }
            }

            // Remove from Groups
            JTable::addIncludePath(WF_ADMINISTRATOR . DS . 'groups');
            $rows = JTable::getInstance('profiles', 'WFTable');

            $query = 'SELECT id, name, plugins, rows'
                    . ' FROM #__wf_profiles';
            $db->setQuery($query);
            $profiles = $db->loadObjectList();

            foreach ($profiles as $profile) {
                $plugins = explode(',', $profile->plugins);
                // Existence check
                if (in_array($this->get('plugin'), $plugins)) {
                    // Load tables
                    $rows->load($profile->id);
                    // Remove from plugins list
                    foreach ($plugins as $k => $v) {
                        if ($this->get('plugin') == $v) {
                            unset($plugins[$k]);
                        }
                    }
                    $rows->plugins = implode(',', $plugins);
                    // Remove from rows
                    if ($this->get('icon')) {
                        $lists = array();
                        foreach (explode(';', $profile->rows) as $list) {
                            $icons = explode(',', $list);
                            foreach ($icons as $k => $v) {
                                if ($this->get('plugin') == $v) {
                                    unset($icons[$k]);
                                }
                            }
                            $lists[] = implode(',', $icons);
                        }
                        $rows->rows = implode(';', $lists);
                    }
                    if (!$rows->store()) {
                        JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . JText::sprintf('WF_INSTALLER_REMOVE_FROM_GROUP_ERROR', $prows->name));
                    }
                }
            }
        } else {
            JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_MANIFEST_ERROR'));
            $retval = false;
        }
        // remove the plugin folder
        if (!JFolder::delete($this->parent->getPath('extension_root'))) {
            JError::raiseWarning(100, WFText::_('WF_INSTALLER_PLUGIN_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_PLUGIN_FOLDER_ERROR'));
            $retval = false;
        }

        return $retval;
    }

}