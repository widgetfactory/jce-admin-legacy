<?php
/**
 * @version		$Id: config.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   JCE
 * @copyright Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   GNU/GPL 2 or later
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
class WFControllerConfig extends WFController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{		
		parent::__construct();
		
		$this->registerTask( 'apply', 'save' );
	}
	
	function display()
	{
		parent::display();
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('RESTRICTED');

		$db 	= JFactory::getDBO();

		$task 	= $this->getTask();

		$client = JRequest::getWord( 'client', 'site' );
		
		// get params
		$component 	= WFExtensionHelper::getComponent();
		// create params object from json string
		$params 	= json_decode($component->params);

		$registry = new JRegistry();
		$registry->loadArray(JRequest::getVar('params', '', 'POST', 'ARRAY'));
		// set preference object
		$params->editor = $registry->toObject();
		// set params as JSON string
		$component->params = json_encode($params);

		if (!$component->check()) {
			JError::raiseError(500, $component->getError() );
		}
		if (!$component->store()) {
			JError::raiseError(500, $component->getError() );
		}
		$component->checkin();
	
		$msg = JText::sprintf('WF_CONFIG_SAVED');		
	
		switch ( $task )
		{
			case 'apply':
				$this->setRedirect( 'index.php?option=com_jce&view=config', $msg );
				break;

			case 'save':
			default:
				$this->setRedirect( 'index.php?option=com_jce&view=cpanel', $msg );
				break;
		}
	}
}
?>