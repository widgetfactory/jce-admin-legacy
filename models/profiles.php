<?php
/**
 * @package   	JCE
 * @copyright 	Copyright � 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

// load base model
require_once(dirname(__FILE__) . DS . 'model.php');

/**
 * Profiles Model
 *
 * @package    JCE
 * @subpackage Components
 */
class WFModelProfiles extends WFModel
{
    /**
     * Get a profile by id
     * @param object $id
     * @return 
     */
    function getUserProfileFromId($id)
    {
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
    function getUserProfileFromType($type)
    {
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
    function getRowArray($rows)
    {
        $out  = array();
        $rows = explode(';', $rows);
        $i    = 1;
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
    function getExtensions($plugin)
    {
        $model 	= JModel::getInstance('plugins', 'WFModel');
        
        $types 		= array();
        $extensions = array();
		$supported  = '';
		
		$manifest = WF_EDITOR_PLUGINS . DS . $plugin . DS . $plugin . '.xml';
		
		if (is_file($manifest)) {
			$xml = WFXMLElement::getXML($manifest);
			
			// get the plugin xml file    
	        if ($xml) {
	            $supported = (string)$xml->extensions;
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
    
    function getPlugins($plugins = false)
    {
        $model = JModel::getInstance('plugins', 'WFModel');
        
        $commands = array();
        
        if (!$plugins) {
            $commands = $model->getCommands();
        }
        
        $plugins = $model->getPlugins();
		// only need plugins with xml files
       	foreach($plugins as $plugin => $properties) {
       		if (!is_file(WF_EDITOR_PLUGINS . DS . $plugin . DS . $plugin . '.xml')) {
       			unset($plugins[$plugin]);
       		}
       	}
        
        return array_merge($commands, $plugins);
    }
    
    function getUserGroups($area)
    {
        $db = JFactory::getDBO();
        
        if (WF_JOOMLA15) {
            $front = array(
                '19',
                '20',
                '21'
            );
            $back  = array(
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
            $back  = array();
            
            foreach ($groups as $group) {
                $create = JAccess::checkGroup($group, 'core.create');
                $admin  = JAccess::checkGroup($group, 'core.login.admin');
                $super  = JAccess::checkGroup($group, 'core.admin');
                
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
     * Process import data from XML file
     * @param object $file XML file
     * @param boolean $install Can be used by the package installer
     * @return 
     */
    function processImport($file, $install = false)
    {
        $mainframe 	= JFactory::getApplication();
        $db 		= JFactory::getDBO();
        $view 		= JRequest::getCmd('view');
        
        $return = false;
        
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);
        
        $xml = WFXMLElement::getXML($file);
        
        if ($xml) {
            $n = 0;
            
            foreach ($xml->profiles->children() as $profile) {
                $row = JTable::getInstance('profiles', 'WFTable');
                // get profile name                 
                $name  = (string)$profile->attributes()->name;
				
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
                    switch ($item->name()) {
						case 'name':
							$name = $item->data();
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
                            $row->description = WFText::_($item->data());
                            
                            break;
                        case 'types':
                            if (!$item->data()) {
                                $area = $profile->area[0]->data();
                                
                                $groups = $this->getUserGroups($area);
                                $data   = implode(',', array_unique($groups));
                                
                            } else {
                                $data = $item->data();
                            }
                            $row->types = $data;
                            break;
                        case 'params':
                            $params = array();
                            foreach ($item->children() as $param) {
                                $params[] = $param->data();
                            }
                            $row->params = implode("\n", $params);
                            
                            break;
                        case 'rows':
  
                            $row->rows = $item->data();
                            
                            break;
                        case 'plugins':
                            $row->plugins = $item->data();
                            
                            break;
                        default:
                            $key       = $item->name();
                            $row->$key = $item->data();
                            
                            break;
                    }
                }
                
                if (!$row->store()) {
                    $mainframe->enqueueMessage(WFText::_('WF_PROFILES_IMPORT_ERROR'), $row->getError(), 'error');
                    return false;
                } else {
                    $n++;
                }
            }
            if ($install) {
                return true;
            } else {
                $mainframe->enqueueMessage(JText::sprintf('WF_PROFILES_IMPORT_SUCCESS', $n));
            }
        }
        if (!$install) {
            $mainframe->redirect('index.php?option=com_jce&view=' . $view);
        }
    }
    
    /**
     * Get default profile data
     * @return $row  Profile table object
     */
    function getDefaultProfile()
    {
        $mainframe = JFactory::getApplication();
        $file = JPATH_COMPONENT . DS . 'models' . DS . 'profiles.xml';
        
        $xml = WFXMLElement::getXML($file);
        
        if ($xml) {            
            foreach ($xml->profiles->children() as $profile) {
                if ($profile->attributes()->default) {
                    $row = JTable::getInstance('profiles', 'WFTable');
                    
                    foreach ($profile->children() as $item) {
                        switch ($item->name()) {
                            case 'rows':
                            $row->rows = $item->data(); 
                            break;
                        	case 'plugins':
                            $row->plugins = $item->data();
                            break;
                        	default:
                            $key       = $item->name();
                            $row->$key = $item->data();
                            
                            break;
                        }
                    }
                    // reset name and description
                    $row->name        = '';
                    $row->description = '';
                    
                    return $row;
                }
            }
        }
        return null;
    }
}