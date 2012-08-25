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

/**
 * Installer function
 * @return
 */
function com_install() {

    if (!defined('JPATH_PLATFORM')) {
        $installer = JInstaller::getInstance();
        return WFInstall::install($installer);
    }

    return true;
}

/**
 * Uninstall function
 * @return
 */
function com_uninstall() {

    if (!defined('JPATH_PLATFORM')) {
        $installer = JInstaller::getInstance();
        return WFInstall::uninstall();
    }

    return true;
}

class WFInstall {

    private static function cleanupInstall() {
        $path = JPATH_ADMINISTRATOR . '/components/com_jce';

        if (!is_file($path . '/jce.php')) {
            self::removePackages();

            $db = JFactory::getDBO();

            // cleanup menus
            if (defined('JPATH_PLATFORM')) {
                $db->setQuery('DELETE FROM #__menu WHERE alias = ' . $db->Quote('jce') . ' AND menutype = ' . $db->Quote('main'));
                $db->query();

                $db->setQuery('DELETE FROM #__menu WHERE alias LIKE ' . $db->Quote('wf-menu-%') . ' AND menutype = ' . $db->Quote('main'));
                $db->query();
            } else {
                $db->setQuery('DELETE FROM #__components WHERE `option` = ' . $db->Quote('com_jce'));
                $db->query();
            }
        }

        if (is_file($path . '/install.script.php')) {
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            JFile::delete($path . '/install.script.php');
            JFolder::delete($path);
        }
    }

    public static function install($installer) {
        error_reporting(E_ERROR | E_WARNING);

        // load languages
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR, null, true);
        $language->load('com_jce.sys', JPATH_ADMINISTRATOR, null, true);

        $requirements = array();

        // check PHP version
        if (version_compare(PHP_VERSION, '5.2.4', '<')) {
            $requirements[] = array(
                'name' => 'PHP Version',
                'info' => 'JCE Requires PHP version 5.2.4 or later. Your version is : ' . PHP_VERSION
            );
        }

        // check JSON is installed
        if (function_exists('json_encode') === false || function_exists('json_decode') === false) {
            $requirements[] = array(
                'name' => 'JSON',
                'info' => 'JCE requires the <a href="http://php.net/manual/en/book.json.php" target="_blank">PHP JSON</a> extension which is not available on this server.'
            );
        }

        // check SimpleXML
        if (function_exists('simplexml_load_string') === false || function_exists('simplexml_load_file') === false || class_exists('SimpleXMLElement') === false) {
            $requirements[] = array(
                'name' => 'SimpleXML',
                'info' => 'JCE requires the <a href="http://php.net/manual/en/book.simplexml.php" target="_blank">PHP SimpleXML</a> library which is not available on this server.'
            );
        }

        if (!empty($requirements)) {
            $message = '<div id="jce"><style type="text/css" scoped="scoped">' . file_get_contents(dirname(__FILE__) . '/media/css/install.css') . '</style>';

            $message .= '<h2>' . JText::_('WF_ADMIN_TITLE') . ' - Install Failed</h2>';
            $message .= '<h3>JCE could not be installed as this site does not meet <a href="http://www.joomlacontenteditor.net/support/documentation/56-editor/106-requirements" target="_blank">technical requirements</a> (see below)</h3>';
            $message .= '<ul class="install">';

            foreach ($requirements as $requirement) {
                $message .= '<li class="error">' . $requirement['name'] . ' : ' . $requirement['info'] . '<li>';
            }

            $message .= '</ul>';
            $message .= '</div>';

            $installer->set('message', $message);

            $installer->abort();

            self::cleanupInstall();

            return false;
        }

        require_once($installer->getPath('extension_administrator') . '/includes/base.php');

        $manifest = $installer->get('manifest');

        // get the version we're installing
        if ($manifest) {
            $new_version = $manifest->version;
        } else {
            $manifest = JApplicationHelper::parseXMLInstallFile($installer->getPath('source') . '/jce.xml');
            $new_version = $manifest['version'];
        }

        $state = false;

        // the current version
        $current_version = $new_version;

        // get the current version 
        $xml_file = $installer->getPath('extension_administrator') . '/jce.xml';

        if (is_file($xml_file)) {
            $xml = JApplicationHelper::parseXMLInstallFile($xml_file);

            if (preg_match('/([0-9\.]+)(beta|rc|dev|alpha)?([0-9]+?)/i', $xml['version'])) {
                // component version is less than current
                if (version_compare($xml['version'], $new_version, '<')) {
                    $current_version = $xml['version'];
                }
                // invalid component version, check for groups table
            } else {
                // check for old tables
                if (self::checkTable('#__jce_groups')) {
                    $current_version = '1.5.0';
                }

                // check for old tables
                if (self::checkTable('#__jce_profiles')) {
                    $current_version = '2.0.0beta1';
                }
            }
        } else {
            // check for old tables
            if (self::checkTable('#__jce_groups')) {
                $current_version = '1.5.0';
            }
        }

        // perform upgrade
        if (version_compare($current_version, $new_version, '<')) {
            $state = self::upgrade($current_version);
        } else {
            // install plugins first
            $state = self::installProfiles();
        }

        if ($state) {
            // legacy (JCE 1.5) cleanup
            if (!defined('JPATH_PLATFORM')) {
                self::legacyCleanup();
            }

            $message = '<div id="jce"><style type="text/css" scoped="scoped">' . file_get_contents(dirname(__FILE__) . '/media/css/install.css') . '</style>';

            $message .= '<h2>' . JText::_('WF_ADMIN_TITLE') . ' ' . $new_version . '</h2>';
            $message .= '<ul class="install">';
            $message .= '<li class="success">' . JText::_('WF_ADMIN_DESC') . '<li>';

            // install packages (editor plugin, quickicon etc)
            $packages = dirname(__FILE__) . '/packages';

            // install additional packages
            if (is_dir($packages)) {
                $message .= self::installPackages($packages);
            }

            $message .= '</ul>';
            $message .= '</div>';

            $installer->set('message', $message);

            // post-install
            self::addIndexfiles(array(dirname(__FILE__), JPATH_SITE . '/components/com_jce', JPATH_PLUGINS . '/jce'));
        } else {
            $installer->abort();

            return false;
        }
    }

    public static function uninstall() {
        $db = JFactory::getDBO();

        // remove Profiles table if its empty
        $query = 'SELECT COUNT(id) FROM #__wf_profiles';
        $db->setQuery($query);

        if (!$db->loadResult()) {
            if (method_exists($db, 'dropTable')) {
                $db->dropTable('#__wf_profiles');
            } else {
                $query = 'DROP TABLE IF EXISTS #__wf_profiles';
                $db->setQuery($query);
            }

            $db->query();
        }
        // remove packages
        self::removePackages();
    }

    /**
     * Upgrade database tables and remove legacy folders
     * @return Boolean
     */
    private static function upgrade($version) {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $admin = JPATH_ADMINISTRATOR . '/components/com_jce';
        $site = JPATH_SITE . '/components/com_jce';

        require_once($admin . '/helpers/parameter.php');

        // add tables path
        JTable::addIncludePath($admin . '/tables');

        // upgrade from 1.5.x to 2.0.0 (only in Joomla! 1.5)
        if (version_compare($version, '2.0.0', '<') && !defined('JPATH_PLATFORM')) {
            // check for groups table / data
            if (self::checkTable('#__jce_groups') && self::checkTableContents('#__jce_groups')) {
                jimport('joomla.plugin.helper');

                // get plugin
                $plugin = JPluginHelper::getPlugin('editors', 'jce');
                // get JCE component
                $table = JTable::getInstance('component');
                $table->loadByOption('com_jce');
                // process params to JSON string
                $params = WFParameterHelper::toObject($table->params);
                // set params
                $table->params = json_encode(array('editor' => $params));
                // store
                $table->store();
                // get all groups data
                $query = 'SELECT * FROM #__jce_groups';
                $db->setQuery($query);
                $groups = $db->loadObjectList();

                // get all plugin data
                $query = 'SELECT id, name, icon FROM #__jce_plugins';
                $db->setQuery($query);
                $plugins = $db->loadAssocList('id');

                $map = array('advlink' => 'link', 'advcode' => 'source', 'tablecontrols' => 'table', 'styleprops' => 'style');

                if (self::createProfilesTable()) {
                    foreach ($groups as $group) {
                        $row = JTable::getInstance('profiles', 'WFTable');

                        $rows = array();

                        // transfer row ids to names
                        foreach (explode(';', $group->rows) as $item) {
                            $icons = array();
                            foreach (explode(',', $item) as $id) {
                                // spacer
                                if ($id == '00') {
                                    $icon = 'spacer';
                                } else {
                                    if (isset($plugins[$id])) {
                                        $icon = $plugins[$id]['icon'];

                                        // map old icon names to new
                                        if (isset($map[$icon])) {
                                            $icon = $map[$icon];
                                        }
                                    }
                                }
                                $icons[] = $icon;
                            }

                            $rows[] = str_replace(array('cite,abbr,acronym,del,ins,attribs', 'search,replace', 'ltr,rtl', 'readmore,pagebreak', 'cut,copy,paste'), array('xhtmlxtras', 'searchreplace', 'directionality', 'article', 'paste'), implode(',', $icons));
                        }
                        // re-assign rows
                        $row->rows = implode(';', $rows);

                        $names = array('anchor');

                        // transfer plugin ids to names
                        foreach (explode(',', $group->plugins) as $id) {
                            if (isset($plugins[$id])) {
                                $name = $plugins[$id]['name'];

                                // map old icon names to new
                                if (isset($map[$name])) {
                                    $name = $map[$name];
                                }

                                $names[] = $name;
                            }
                        }
                        // re-assign plugins
                        $row->plugins = implode(',', $names);

                        // convert params to JSON
                        $params = WFParameterHelper::toObject($group->params);
                        $data = new StdClass();

                        foreach ($params as $key => $value) {
                            $parts = explode('_', $key);

                            $node = array_shift($parts);

                            // special consideration for imgmanager_ext!!
                            if (strpos($key, 'imgmanager_ext_') !== false) {
                                $node = $node . '_' . array_shift($parts);
                            }

                            // convert some nodes
                            if (isset($map[$node])) {
                                $node = $map[$node];
                            }

                            $key = implode('_', $parts);

                            if ($value !== '') {
                                if (!isset($data->$node) || !is_object($data->$node)) {
                                    $data->$node = new StdClass();
                                }
                                // convert Link parameters
                                if ($node == 'link' && $key != 'target') {
                                    $sub = $key;
                                    $key = 'links';

                                    if (!isset($data->$node->$key)) {
                                        $data->$node->$key = new StdClass();
                                    }

                                    if (preg_match('#^(content|contacts|static|weblinks|menu)$#', $sub)) {
                                        if (!isset($data->$node->$key->joomlalinks)) {
                                            $data->$node->$key->joomlalinks = new StdClass();
                                            $data->$node->$key->joomlalinks->enable = 1;
                                        }
                                        $data->$node->$key->joomlalinks->$sub = $value;
                                    } else {
                                        $data->$node->$key->$sub = new StdClass();
                                        $data->$node->$key->$sub->enable = 1;
                                    }
                                } else {
                                    $data->$node->$key = $value;
                                }
                            }
                        }
                        // re-assign params
                        $row->params = json_encode($data);

                        // re-assign other values
                        $row->name = $group->name;
                        $row->description = $group->description;
                        $row->users = $group->users;
                        $row->types = $group->types;
                        $row->components = $group->components;
                        $row->published = $group->published;
                        $row->ordering = $group->ordering;

                        // add area data
                        if ($row->name == 'Default') {
                            $row->area = 0;
                        }

                        if ($row->name == 'Front End') {
                            $row->area = 1;
                        }

                        if (self::checkTable('#__wf_profiles')) {
                            $name = $row->name;

                            // check for existing profile
                            $query = 'SELECT id FROM #__wf_profiles' . ' WHERE name = ' . $db->Quote($name);
                            $db->setQuery($query);
                            // create name copy if exists
                            while ($db->loadResult()) {
                                $name = JText::sprintf('WF_PROFILES_COPY_OF', $name);

                                $query = 'SELECT id FROM #__wf_profiles' . ' WHERE name = ' . $db->Quote($name);

                                $db->setQuery($query);
                            }
                            // set name
                            $row->name = $name;
                        }

                        if (!$row->store()) {
                            $app->enqueueMessage('Conversion of group data failed : ' . $row->name, 'error');
                        } else {
                            $app->enqueueMessage('Conversion of group data successful : ' . $row->name);
                        }

                        unset($row);
                    }

                    // Drop tables
                    $query = 'DROP TABLE IF EXISTS #__jce_groups';
                    $db->setQuery($query);
                    $db->query();

                    // If profiles table empty due to error, install profiles data
                    if (!self::checkTableContents('#__wf_profiles')) {
                        self::installProfiles();
                    }
                } else {
                    return false;
                }
                // Install profiles
            } else {
                self::installProfiles();
            }

            // Drop tables
            $query = 'DROP TABLE IF EXISTS #__jce_plugins';
            $db->setQuery($query);
            $db->query();

            // Drop tables
            $query = 'DROP TABLE IF EXISTS #__jce_extensions';
            $db->setQuery($query);
            $db->query();

            // Remove Plugins menu item
            $query = 'DELETE FROM #__components' . ' WHERE admin_menu_link = ' . $db->Quote('option=com_jce&type=plugins');

            $db->setQuery($query);
            $db->query();

            // Update Component Name
            $query = 'UPDATE #__components' . ' SET name = ' . $db->Quote('COM_JCE') . ' WHERE ' . $db->Quote('option') . '=' . $db->Quote('com_jce') . ' AND parent = 0';

            $db->setQuery($query);
            $db->query();

            // Fix links for other views and edit names
            $menus = array('install' => 'installer', 'group' => 'profiles', 'groups' => 'profiles', 'config' => 'config');

            $row = JTable::getInstance('component');

            foreach ($menus as $k => $v) {
                $query = 'SELECT id FROM #__components' . ' WHERE admin_menu_link = ' . $db->Quote('option=com_jce&type=' . $k);
                $db->setQuery($query);
                $id = $db->loadObject();

                if ($id) {
                    $row->load($id);
                    $row->name = $v;
                    $row->admin_menu_link = 'option=com_jce&view=' . $v;

                    if (!$row->store()) {
                        $mainframe->enqueueMessage('Unable to update Component Links for view : ' . strtoupper($v), 'error');
                    }
                }
            }

            // remove old admin language files
            $folders = JFolder::folders(JPATH_ADMINISTRATOR . '/language', '.', false, true, array('.svn', 'CVS', 'en-GB'));

            foreach ($folders as $folder) {
                $name = basename($folder);
                $files = array($name . '.com_jce.ini', $name . '.com_jce.menu.ini', $name . '.com_jce.xml');
                foreach ($files as $file) {
                    if (is_file($folder . '/' . $file)) {
                        @JFile::delete($folder . '/' . $file);
                    }
                }
            }

            // remove old site language files
            $folders = JFolder::folders(JPATH_SITE . '/language', '.', false, true, array('.svn', 'CVS', 'en-GB'));

            foreach ($folders as $folder) {
                $files = JFolder::files($folder, '^' . basename($folder) . '\.com_jce([_a-z0-9]+)?\.(ini|xml)$', false, true);
                @JFile::delete($files);
            }

            // remove legacy admin folders
            $folders = array('cpanel', 'config', 'css', 'groups', 'plugins', 'img', 'installer', 'js');
            foreach ($folders as $folder) {
                if (is_dir($admin . '/' . $folder)) {
                    @JFolder::delete($admin . '/' . $folder);
                }
            }

            // remove legacy admin files
            $files = array('editor.php', 'helper.php', 'updater.php');

            foreach ($files as $file) {
                if (is_file($admin . '/' . $file)) {
                    @JFile::delete($admin . '/' . $file);
                }
            }

            // remove legacy admin folders
            $folders = array('controller', 'css', 'js');
            foreach ($folders as $folder) {
                if (is_dir($site . '/' . $folder)) {
                    @JFolder::delete($site . '/' . $folder);
                }
            }

            // remove legacy admin files
            $files = array('popup.php');

            foreach ($files as $file) {
                if (is_file($site . '/' . $file)) {
                    @JFile::delete($site . '/' . $file);
                }
            }


            if (!defined('JPATH_PLATFORM')) {
                // remove old plugin folder
                $path = JPATH_PLUGINS . '/editors';

                if (is_dir($path . '/jce')) {
                    @JFolder::delete($path . '/jce');
                }
            }

            return true;
        }// end JCE 1.5 upgrade
        // cleanup javascript and css files moved to site
        if (version_compare($version, '2.0.10', '<')) {
            $path = $admin . '/media';

            $scripts = array('colorpicker.js', 'help.js', 'html5.js', 'select.js', 'tips.js');

            foreach ($scripts as $script) {
                if (is_file($path . '/js/' . $script)) {
                    @JFile::delete($path . '/js/' . $script);
                }
            }

            if (is_dir($path . '/js/jquery')) {
                @JFolder::delete($path . '/js/jquery');
            }

            $styles = array('help.css', 'select.css', 'tips.css');

            foreach ($styles as $style) {
                if (is_file($path . '/css/' . $style)) {
                    @JFile::delete($path . '/css/' . $style);
                }
            }

            // delete jquery
            if (is_dir($path . '/css/jquery')) {
                @JFolder::delete($path . '/css/jquery');
            }

            // remove popup controller
            if (is_dir($site . '/controller')) {
                @JFolder::delete($site . '/controller');
            }
        }

        // delete error.php file
        if (version_compare($version, '2.0.12', '<')) {
            if (is_file($site . '/editor/libraries/classes/error.php')) {
                @JFile::delete($site . '/editor/libraries/classes/error.php');
            }
        }

        if (version_compare($version, '2.1', '<')) {
            if (is_dir($admin . '/plugin')) {
                @JFolder::delete($admin . '/plugin');
            }

            // Add Visualblocks plugin
            $query = 'SELECT id FROM #__wf_profiles';
            $db->setQuery($query);
            $profiles = $db->loadObjectList();

            $profile = JTable::getInstance('Profiles', 'WFTable');

            if (!empty($profiles)) {
                foreach ($profiles as $item) {
                    $profile->load($item->id);

                    if (strpos($profile->rows, 'visualblocks') === false) {
                        $profile->rows = str_replace('visualchars', 'visualchars,visualblocks', $profile->rows);
                    }
                    if (strpos($profile->plugins, 'visualblocks') === false) {
                        $profile->plugins = str_replace('visualchars', 'visualchars,visualblocks', $profile->plugins);
                    }

                    $profile->store();
                }
            }
        }

        if (version_compare($version, '2.1.1', '<')) {
            @JFile::delete($admin . '/classes/installer.php');

            // Add Visualblocks plugin
            $query = 'SELECT id FROM #__wf_profiles';
            $db->setQuery($query);
            $profiles = $db->loadObjectList();

            $profile = JTable::getInstance('Profiles', 'WFTable');

            if (!empty($profiles)) {
                foreach ($profiles as $item) {
                    $profile->load($item->id);

                    // add anchor to end of plugins list
                    if (strpos($profile->rows, 'anchor') !== false) {
                        $profile->plugins .= ',anchor';
                    }

                    $profile->store();
                }
            }

            // delete old anchor stuff
            $theme = $site . '/editor/tiny_mce/themes/advanced';

            foreach (array('css/anchor.css', 'js/anchor.js', 'tmpl/anchor.php', 'skins/default/img/items.gif') as $item) {
                if (JFile::exists($theme . '/' . $item)) {
                    @JFile::delete($theme . '/' . $item);
                }
            }

            // delete popup.php
            if (is_file($site . '/popup.php')) {
                @JFile::delete($site . '/popup.php');
            }
        }

        // Add "Blogger" profile and selete some stuff
        if (version_compare($version, '2.2.1', '<')) {
            $path = $site . '/editor/extensions/browser';
            $files = array('css/search.css', 'js/search.js', 'search.php');

            foreach ($files as $file) {
                if (is_file($path . '/' . $file)) {
                    @JFile::delete($path . '/' . $file);
                }
            }

            $query = 'SELECT id FROM #__wf_profiles WHERE name = ' . $db->Quote('Blogger');
            $id = $db->loadResult();

            if (!$id) {
                // Blogger
                $file = $admin . '/models/profiles.xml';

                $xml = WFXMLElement::getXML($file);

                if ($xml) {
                    foreach ($xml->profiles->children() as $profile) {
                        if ($profile->attributes()->name == 'Blogger') {
                            $row = JTable::getInstance('profiles', 'WFTable');

                            foreach ($profile->children() as $item) {
                                switch ($item->getName()) {
                                    case 'rows':
                                        $row->rows = (string) $item;
                                        break;
                                    case 'plugins':
                                        $row->plugins = (string) $item;
                                        break;
                                    default:
                                        $key = $item->getName();
                                        $row->$key = (string) $item;

                                        break;
                                }
                            }
                            $row->store();
                        }
                    }
                }
            }
        }

        // remove k2links from previous version (accidentally installed)
        if (version_compare($version, '2.2.1', '>') && version_compare($version, '2.2.5', '<')) {
            $path = $site . '/editor/extensions/links';

            if (is_file($path . '/k2links.php') && is_file($path . '/k2links.xml') && !is_dir($path . '/k2links')) {
                @JFile::delete($path . '/k2links.php');
                @JFile::delete($path . '/k2links.xml');
            }
        }

        // cleanup old JQuery libraries
        if (version_compare($version, '2.2.6', '<')) {
            $path       = $site . '/editor/libraries/js/jquery';
            $files      = JFolder::files($path, '\.js');
            $exclude    = array('jquery-1.7.2.min.js', 'jquery-ui-1.8.21.custom.min.js', 'jquery-ui-layout.js');

            foreach ($files as $file) {
                if (in_array(basename($file), $exclude) === false) {
                    @JFile::delete($path . '/' . $file);
                }
            }
        }
        
        return true;
    }

    private static function createProfilesTable() {
        // add models path
        JModel::addIncludePath(dirname(__FILE__) . '/models');

        $profiles = JModel::getInstance('profiles', 'WFModel');

        return $profiles->createProfilesTable();
    }

    private static function installProfiles() {
        // add models path
        JModel::addIncludePath(dirname(__FILE__) . '/models');

        $profiles = JModel::getInstance('profiles', 'WFModel');

        return $profiles->installProfiles();
    }

    /**
     * Install additional packages
     * @return Array or false
     * @param object $path[optional] Path to package folder
     */
    private static function installPackages($source) {
        jimport('joomla.installer.installer');

        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();

        $result = '';

        JTable::addIncludePath(JPATH_LIBRARIES . '/joomla/database/table');

        $packages = array(
            'editors' => array('jce'),
            'quickicon' => array('jcefilebrowser'),
            'modules' => array('mod_jcefilebrowser')
        );

        foreach ($packages as $folder => $element) {
            // Joomla! 2.5
            if (defined('JPATH_PLATFORM')) {
                if ($folder == 'modules') {
                    continue;
                }
                // Joomla! 1.5  
            } else {
                if ($folder == 'quickicon') {
                    continue;
                }
            }

            $installer = new JInstaller();
            $installer->setOverwrite(true);

            if ($installer->install($source . '/' . $folder)) {

                if (method_exists($installer, 'loadLanguage')) {
                    $installer->loadLanguage($source . '/' . $folder);
                }

                if ($installer->message) {
                    $result .= '<li class="success">' . JText::_($installer->message, $installer->message) . '</li>';
                }

                // enable quickicon
                if ($folder == 'quickicon') {
                    $plugin = JTable::getInstance('extension');

                    foreach ($element as $item) {
                        $id = $plugin->find(array('type' => 'plugin', 'folder' => $folder, 'element' => $item));

                        $plugin->load($id);
                        $plugin->publish();
                    }
                }
                // enable module
                if ($folder == 'modules') {
                    $module = JTable::getInstance('module');

                    foreach ($element as $item) {
                        $id = self::getModule($item);

                        $module->load($id);
                        $module->position = 'icon';
                        $module->ordering = 100;
                        $module->published = 1;
                        $module->store();
                    }
                }

                // add index files
                self::addIndexfiles(array($installer->getPath('extension_root')));
            } else {
                $result .= '<li class="error">' . JText::_($installer->message, $installer->message) . '</li>';
            }
        }

        return $result;
    }

    private static function getModule($name) {
        // Joomla! 2.5
        if (defined('JPATH_PLATFORM')) {
            $module = JTable::getInstance('extension');
            return $module->find(array('type' => 'module', 'element' => $name));

            // Joomla! 1.5    
        } else {
            $db = JFactory::getDBO();
            $query = 'SELECT id FROM #__modules' . ' WHERE module = ' . $db->Quote($name);

            $db->setQuery($query);
            return $db->loadResult();
        }
    }

    private static function getPlugin($folder, $element) {
        // Joomla! 2.5
        if (defined('JPATH_PLATFORM')) {
            $plugin = JTable::getInstance('extension');
            return $plugin->find(array('type' => 'plugin', 'folder' => $folder, 'element' => $element));
            // Joomla! 1.5    
        } else {
            $plugin = JTable::getInstance('plugin');

            $db = JFactory::getDBO();
            $query = 'SELECT id FROM #__plugins' . ' WHERE folder = ' . $db->Quote($folder) . ' AND element = ' . $db->Quote($element);

            $db->setQuery($query);
            return $db->loadResult();
        }
    }

    /**
     * Uninstall the editor
     * @return boolean
     */
    private static function removePackages() {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();

        jimport('joomla.module.helper');
        jimport('joomla.installer.installer');

        $plugins = array(
            'editors' => array('jce'),
            'quickicon' => array('jcefilebrowser')
        );

        $modules = array('mod_jcefilebrowser');

        // items to remove
        $items = array(
            'plugin' => array(),
            'module' => array()
        );

        foreach ($plugins as $folder => $elements) {
            foreach ($elements as $element) {
                $item = self::getPlugin($folder, $element);

                if ($item) {
                    $items['plugin'][] = $item;
                }
            }
        }

        foreach ($modules as $module) {
            $item = self::getModule($module);

            if ($item) {
                $items['module'][] = $item;
            }
        }

        foreach ($items as $type => $extensions) {
            if ($extensions) {
                foreach ($extensions as $id) {
                    $installer = new JInstaller();
                    $installer->uninstall($type, $id);
                    $app->enqueueMessage($installer->message);
                }
            }
        }
    }

    private static function addIndexfiles($paths) {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        // get the base file
        $file = JPATH_ADMINISTRATOR . '/components' . 'com_jce/index.html';

        if (is_file($file)) {

            foreach ((array) $paths as $path) {
                if (is_dir($path)) {
                    // admin component
                    $folders = JFolder::folders($path, '.', true, true);

                    foreach ($folders as $folder) {
                        JFile::copy($file, $folder . '/' . basename($file));
                    }
                }
            }
        }
    }

    private static function legacyCleanup() {
        $db = JFactory::getDBO();

        // Drop tables
        $query = 'DROP TABLE IF EXISTS #__jce_groups';
        $db->setQuery($query);
        $db->query();

        // Drop tables
        $query = 'DROP TABLE IF EXISTS #__jce_plugins';
        $db->setQuery($query);
        $db->query();
    }

    private static function checkTable($table) {
        $db = JFactory::getDBO();

        $tables = $db->getTableList();

        if (!empty($tables)) {
            // swap array values with keys, convert to lowercase and return array keys as values
            $tables = array_keys(array_change_key_case(array_flip($tables)));
            $app = JFactory::getApplication();
            $match = str_replace('#__', strtolower($app->getCfg('dbprefix', '')), $table);

            return in_array($match, $tables);
        }

        // try with query
        $query = 'SELECT COUNT(id) FROM ' . $table;
        $db->setQuery($query);

        return $db->query();
    }

    /**
     * Check table contents
     * @return boolean
     * @param string $table Table name
     */
    private static function checkTableContents($table) {
        $db = JFactory::getDBO();
        $query = 'SELECT COUNT(id) FROM ' . $table;
        $db->setQuery($query);

        return $db->loadResult();
    }

}

?>