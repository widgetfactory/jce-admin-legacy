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

abstract class WFParameterHelper
{
	/**
	 * Convert JSON data to JParameter Object
	 * @param $data JSON data
	 */
	public static function toObject($data) 
	{
		$param = new JParameter('');
		$param->bind($data);

		return $param->toObject();
	}
	
	public static function getComponentParams($key = '', $path = '')
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'parameter.php');		
		$component = JComponentHelper::getComponent('com_jce');
		
		return new WFParameter($component->params, $path, $key);
	}
}