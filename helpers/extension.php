<?php
/**
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

abstract class WFExtensionHelper {

	public static function getComponent($id =null, $option ='com_jce')
	{

		if(WF_JOOMLA15) {
			// get component table
			$component =JTable::getInstance('component');

			if($id) {
				$component->load($id);
			} else {
				$component->loadByOption($option);
			}
		} else {
			// get component table
			$component =JTable::getInstance('extension');

			if(!$id) {
				$id = $component->find( array('type' => 'component', 'element' => $option));
			}

			$component->load($id);
		}
		
		return $component;
	}

	public static function getPlugin($id =null, $element ='jce', $folder ='editors')
	{

		if(WF_JOOMLA15) {
			$plugin =JTable::getInstance('plugin');

			if(!$id) {
				$db =JFactory::getDBO();
				$query = 'SELECT id FROM #__plugins' . ' WHERE folder = ' . $db->Quote($folder) . ' AND element = ' . $db->Quote($element);

				$db->setQuery($query);
				$id = $db->loadResult();
			}

			$plugin->load($id);

		} else {
			// get component table
			$plugin =JTable::getInstance('extension');

			if(!$id) {
				$id = $plugin->find( array('type' => 'plugin', 'folder' => $folder, 'element' => $element));
			}

			$plugin->load($id);
			// map extension_id to id
			$plugin->id = $plugin->extension_id;
		}

		return $plugin;
	}

}
