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