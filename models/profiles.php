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
require_once(dirname(__FILE__) . '/model.php');

/**
 * Profiles Model
 *
 * @package    JCE
 * @subpackage Components
 */
class WFModelProfiles extends WFModel {

    /**
     * Get a profile by id
     * @param object $id
     * @return 
     */
    function getUserProfileFromId($id) {
        $db = JFactory::getDBO();

        $query = 'SELECT *' . ' FROM #__wf_profiles' . ' WHERE ' . $id . ' IN (users)';
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Get a profile assigned to a user type
     * @param object $type
     * @return 
     */
    function getUserProfileFromType($type) {
        $db = JFactory::getDBO();

        if (!is_int($type)) {
            $query = 'SELECT id' . ' FROM #__core_acl_aro_groups' . ' WHERE name = ' . $db->Quote($type);
            $db->setQuery($query);
            $id = $db->loadResult();
        }

        $query = 'SELECT *' . ' FROM #__wf_profiles' . ' WHERE ' . $type . ' IN (types)';
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Convert row string into array
     * @param object $rows
     * @return 
     */
    function getRowArray($rows) {
        $out = array();
        $rows = explode(';', $rows);
        $i = 1;
        foreach ($rows as $row) {
            $out[$i] = $row;
            $i++;
        }
        return $out;
    }

    /**
     * Get a plugin's extensions
     * @param object $plugin
     * @return 
     */
    function getExtensions($plugin) {
        $model = JModel::getInstance('plugins', 'WFModel');

        $types = array();
        $extensions = array();
        $supported = '';

        $manifest = WF_EDITOR_PLUGINS . '/' . $plugin . '/' . $plugin . '.xml';

        if (is_file($manifest)) {
            $xml = WFXMLElement::getXML($manifest);

            // get the plugin xml file    
            if ($xml) {
                $supported = (string) $xml->extensions;
            }
        }

        // get extensions supported by the plugin
        if ($supported) {
            $types = explode(',', $supported);
        }

        foreach ($model->getExtensions() as $extension) {
            // filter by plugin
            if (!empty($extension->plugins)) {
                // extension only supports specific plugins
                if (in_array($plugin, $extension->plugins)) {
                    if (!empty($types) && in_array($extension->folder, $types)) {
                        $extensions[] = $extension;
                    }
                }
                // extension potentially supports all plugins
            } else {
                if (!empty($types) && in_array($extension->folder, $types)) {
                    $extensions[] = $extension;
                }
            }
        }

        return $extensions;
    }

    function getPlugins($plugins = array()) {
        $model = JModel::getInstance('plugins', 'WFModel');

        $commands = array();

        if (empty($plugins)) {
            $commands = $model->getCommands();
        }

        // only need plugins with xml files
        foreach ($model->getPlugins() as $plugin => $properties) {            
            if (is_file(JPATH_SITE . $properties->path . '/' . $plugin . '.xml')) {
                $plugins[$plugin] = $properties;
            }
        }

        return array_merge($commands, $plugins);
    }

    function getUserGroups($area) {
        $db = JFactory::getDBO();

        if (WF_JOOMLA15) {
            $front = array(
                '19',
                '20',
                '21'
            );
            $back = array(
                '23',
                '24',
                '25'
            );
        } else {
            jimport('joomla.access.access');

            $query = 'SELECT id FROM #__usergroups';
            $db->setQuery($query);
            $groups = $db->loadResultArray();

            $front = array();
            $back = array();

            foreach ($groups as $group) {
                $create = JAccess::checkGroup($group, 'core.create');
                $admin = JAccess::checkGroup($group, 'core.login.admin');
                $super = JAccess::checkGroup($group, 'core.admin');

                if ($super) {
                    $back[] = $group;
                } else {
                    // group can create
                    if ($create) {
                        // group has admin access
                        if ($admin) {
                            $back[] = $group;
                        } else {
                            $front[] = $group;
                        }
                    }
                }
            }
        }

        switch ($area) {
            case 0:
                return array_merge($front, $back);
                break;
            case 1:
                return $front;
                break;
            case 2:
                return $back;
                break;
        }

        return array();
    }
    
    /**
     * Create the Profiles table
     * @return boolean
     */
    public function createProfilesTable() {
        jimport('joomla.installer.helper');

        $mainframe = JFactory::getApplication();

        $db = JFactory::getDBO();
        $driver = strtolower($db->name);

        switch ($driver) {
            default :
            case 'mysqli' :
                $driver = 'mysql';
                break;
            case 'sqlazure' :
                $driver = 'sqlsrv';
                break;
        }
        // speed up for mysql - most common
        if ($driver == 'mysql') {
            $query = "CREATE TABLE IF NOT EXISTS `#__wf_profiles` (
	        `id` int(11) NOT NULL AUTO_INCREMENT,
	        `name` varchar(255) NOT NULL,
	        `description` varchar(255) NOT NULL,
	        `users` text NOT NULL,
	        `types` varchar(255) NOT NULL,
	        `components` text NOT NULL,
	        `area` tinyint(3) NOT NULL,
	        `rows` text NOT NULL,
	        `plugins` text NOT NULL,
	        `published` tinyint(3) NOT NULL,
	        `ordering` int(11) NOT NULL,
	        `checked_out` tinyint(3) NOT NULL,
	        `checked_out_time` datetime NOT NULL,
	        `params` text NOT NULL,
	        PRIMARY KEY (`id`)
	        );";
            $db->setQuery($query);

            if ($db->query()) {
                return true;
            } else {
                $error = $db->stdErr();
            }
            // sqlsrv
        } else {
            $file = dirname(dirname(__FILE__)) . '/sql/' . $driver . '.sql';
            $error = null;

            if (is_file($file)) {
                $buffer = file_get_contents($file);

                if ($buffer) {
                    $queries = JInstallerHelper::splitSql($buffer);

                    if (count($queries)) {
                        $query = $queries[0];

                        if ($query) {
                            $db->setQuery(trim($query));

                            if (!$db->query()) {
                                $mainframe->enqueueMessage(WFText::_('WF_INSTALL_TABLE_PROFILES_ERROR') . $db->stdErr(), 'error');
                                return false;
                            } else {
                                return true;
                            }
                        } else {
                            $error = 'NO SQL QUERY';
                        }
                    } else {
                        $error = 'NO SQL QUERIES';
                    }
                } else {
                    $error = 'SQL FILE EMPTY';
                }
            } else {
                $error = 'SQL FILE MISSING';
            }
        }

        $mainframe->enqueueMessage(WFText::_('WF_INSTALL_TABLE_PROFILES_ERROR') . !is_null($error) ? ' - ' . $error : '', 'error');
        return false;
    }
    
    /**
     * Install Profiles
     * @return boolean
     * @param object $install[optional]
     */
    public function installProfiles() {
        $app    = JFactory::getApplication();
        $db     = JFactory::getDBO();

        if ($this->createProfilesTable()) {
            $query = 'SELECT COUNT(id) FROM #__wf_profiles';
            $db->setQuery($query);

            $profiles = array('Default' => false, 'Front End' => false);

            // No Profiles table data
            if (!$db->loadResult()) {
                $xml = dirname(__FILE__) . '/profiles.xml';

                if (is_file($xml)) {
                    if (!$this->processImport($xml)) {
                        $app->enqueueMessage(WFText::_('WF_INSTALL_PROFILES_ERROR'), 'error');
                        
                        return false;
                    }
                } else {
                    $app->enqueueMessage(WFText::_('WF_INSTALL_PROFILES_NOFILE_ERROR'), 'error');
                    
                    return false;
                }
            }
            
           return true;
        }

        return false;
    }

    /**
     * Process import data from XML file
     * @param object $file XML file
     * @param boolean $install Can be used by the package installer
     * @return 
     */
    public function processImport($file) {
        $app    = JFactory::getApplication();
        $db     = JFactory::getDBO();
        $view   = JRequest::getCmd('view');

        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);
        
        JTable::addIncludePath(dirname(dirname(__FILE__)) . '/tables');

        $xml = WFXMLElement::getXML($file);

        if ($xml) {
            $n = 0;

            foreach ($xml->profiles->children() as $profile) {
                $row = JTable::getInstance('profiles', 'WFTable');
                // get profile name                 
                $name = (string) $profile->attributes()->name;

                // backwards compatability
                if ($name) {
                    // check for name
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

                foreach ($profile->children() as $item) {
                    switch ($item->getName()) {
                        case 'name':
                            $name = (string) $item;
                            // only if name set and table name not set
                            if ($name && !$row->name) {
                                // check for name
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

                            break;
                        case 'description':
                            $row->description = WFText::_((string) $item);

                            break;
                        case 'types':
                            if (!(string) $item) {
                                $area = (string) $profile->area[0];

                                $groups = $this->getUserGroups($area);
                                $data 	= implode(',', array_unique($groups));
                            } else {
                                $data = (string) $item;
                            }
                            $row->types = $data;
                            break;
                        case 'params':
                            $params = array();
                            foreach ($item->children() as $param) {
                                $params[] = (string) $param;
                            }
                            $row->params = implode("\n", $params);

                            break;
                        case 'rows':

                            $row->rows = (string) $item;

                            break;
                        case 'plugins':
                            $row->plugins = (string) $item;

                            break;
                        default:
                            $key 		= $item->getName();
                            $row->$key 	= (string) $item;

                            break;
                    }
                }

                if (!$row->store()) {
                    $app->enqueueMessage(WFText::_('WF_PROFILES_IMPORT_ERROR'), $row->getError(), 'error');
                    return false;
                } else {
                    $n++;
                }
            }
            return true;
        }
    }

    /**
     * Get default profile data
     * @return $row  Profile table object
     */
    function getDefaultProfile() {
        $mainframe = JFactory::getApplication();
        $file = JPATH_COMPONENT . '/models/profiles.xml';

        $xml = WFXMLElement::getXML($file);

        if ($xml) {
            foreach ($xml->profiles->children() as $profile) {
                if ($profile->attributes()->default) {
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
                                $key 		= $item->getName();
                                $row->$key 	= (string) $item;

                                break;
                        }
                    }
                    // reset name and description
                    $row->name = '';
                    $row->description = '';

                    return $row;
                }
            }
        }
        return null;
    }

    function getEditorParams(&$row) {
        // get params definitions
        $xml = WF_EDITOR_LIBRARIES . '/xml/config/profiles.xml';

        // get editor params
        $params = new WFParameter($row->params, $xml, 'editor');
        $params->addElementPath(JPATH_COMPONENT . '/elements');
        $params->addElementPath(WF_EDITOR . '/elements');

        $groups = $params->getGroups();

        $row->editor_params = $params;
        $row->editor_groups = $groups;
    }

    function getLayoutParams(&$row) {
        // get params definitions
        $xml = WF_EDITOR_LIBRARIES . '/xml/config/layout.xml';

        // get editor params
        $params = new WFParameter($row->params, $xml, 'editor');
        $params->addElementPath(JPATH_COMPONENT . '/elements');
        $params->addElementPath(WF_EDITOR . '/elements');

        $groups = $params->getGroups();

        $row->layout_params = $params;
        $row->layout_groups = $groups;
    }

    function getPluginParameters() {
        
    }

    function getThemes() {
        jimport('joomla.filesystem.folder');
        $path = WF_EDITOR_THEMES . '/advanced/skins';

        return JFolder::folders($path, '.', false, true);
    }
    
    /**
     * Check whether a table exists
     * @return boolean
     * @param string $table Table name
     */
    public static function checkTable() {
        $db = JFactory::getDBO();

        $tables = $db->getTableList();

        if (!empty($tables)) {
            // swap array values with keys, convert to lowercase and return array keys as values
            $tables = array_keys(array_change_key_case(array_flip($tables)));
            $app = JFactory::getApplication();
            $match = str_replace('#__', strtolower($app->getCfg('dbprefix', '')), '#__wf_profiles');

            return in_array($match, $tables);
        }

        // try with query
        $query = 'SELECT COUNT(id) FROM #__wf_profiles';
        $db->setQuery($query);

        return $db->query();
    }

    /**
     * Check table contents
     * @return boolean
     * @param string $table Table name
     */
    public static function checkTableContents() {        
        $db = JFactory::getDBO();
        $query = 'SELECT COUNT(id) FROM #__wf_profiles';
        $db->setQuery($query);

        return $db->loadResult();
    }
    
    private function getIconType($icon) {
        // TODO - Enhance this later to get the type from xml

        if (in_array($icon, array('styleselect', 'formatselect', 'fontselect', 'fontsizeselect'))) {
            return 'mceListBox';
        }

        if (in_array($icon, array('paste', 'numlist', 'bullist', 'forecolor', 'backcolor', 'spellchecker', 'textcase'))) {
            return 'mceSplitButton';
        }

        return 'mceButton';
    }
    
    public function getIcon($plugin) {
        if ($plugin->type == 'command') {
            $base = 'components/com_jce/editor/tiny_mce/themes/advanced/img';
        } else {
            if (isset($plugin->path)) {
                $base = $plugin->path . '/img/';
            } else {
                $base = 'components/com_jce/editor/tiny_mce/plugins/' . $plugin->name . '/img';
            }
        }

        $span = '';
        $img = '';
        $icons = explode(',', $plugin->icon);

        foreach ($icons as $icon) {
            if ($icon == '|' || $icon == 'spacer') {
                $span .= '<span class="mceSeparator"></span>';
            } else {
                $path = $base . $icon . '.png';

                if (JFile::exists(JPATH_SITE . '/' . $path)) {
                    $img = '<img src="' . JURI::root(true) . $path . '" alt="' . WFText::_($plugin->title) . '" />';
                }

                $span .= '<span title="' . WFText::_($plugin->title) . '::' . WFText::_($plugin->description) . '" class="tooltip tooltip-cancel-ondrag ' . self::getIconType($icon) . '"><span class="mceIcon mce_' . preg_replace('/[^a-z0-9_-]/i', '', $icon) . '">' . $img . '</span></span>';
            }
        }

        return $span;
    }
}