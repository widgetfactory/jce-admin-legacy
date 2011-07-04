<?php
/**
 * @version   $Id: parameter.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

/**
 * Joomla! 1.5 / 1.6 bridging functions
 * @author ryandemmer
 */
class WFParameterHelper
{
	/**
	 * Convert JSON data to JParameter Object
	 * @param $data JSON data
	 */
	function toObject($data) 
	{
		$param = new JParameter('');
		$param->bind($data);

		return $param->toObject();
	}
	
	function getComponentParams($key = '', $path = '')
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'parameter.php');		
		$component = JComponentHelper::getComponent('com_jce');
		
		return new WFParameter($component->params, $path, $key);
	}
}