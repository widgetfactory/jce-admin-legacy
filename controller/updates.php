<?php
/**
 * @version		$Id: updates.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugins Component Controller
 *
 * @package		Joomla
 * @subpackage	Plugins
 * @since 1.5
 */
class WFControllerUpdates extends WFController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{		
		parent::__construct();
	}
	
	function display()
	{
		parent::display();
	}
	
	function update()
	{
		$step 	= JRequest::getWord('step');		
		$model 	=$this->getModel('updates');

		$result = array();
		
		switch ($step) {
			case 'check':
				$result = $model->check();
				break;
			case 'download':
				$result = $model->download();
				break;
			case 'install':
				$result = $model->install();
				break;
		}
		exit($result);
	}
}
?>