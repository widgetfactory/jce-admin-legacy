<?php
/**
 * @version $Id: editor.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

class WFBrowserHelper 
{
	public static function getBrowserLink($element = null, $filter = '')
	{
		require_once(dirname(dirname(__FILE__)) .DS . 'models' .DS. 'model.php');	
						
		$model = JModel::getInstance('WFModel');		
		return $model->getBrowserLink($element, $filter);
	}
}	
	