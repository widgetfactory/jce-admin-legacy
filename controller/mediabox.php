<?php
/**
 * @version		$Id: mediabox.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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

class WFControllerMediabox extends WFController
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
		JRequest::checkToken() or die( 'RESTRICTED' );

		$db = JFactory::getDBO();
		
		if (WF_JOOMLA15) {
			$row = JTable::getInstance('plugin');
			
			$query = 'SELECT id FROM #__plugins'
			. ' WHERE folder = ' . $db->Quote('system')
			. ' AND element = ' . $db->Quote('jcemediabox')
			;
			$db->setQuery($query);
			
			$id = $db->loadResult();
		} else {
			// get component table
			$row = JTable::getInstance('extension');
			
			$id = $row->find(array(
				'type'		=> 'plugin',
				'element' 	=> 'jcemediabox'
			));
		}
		
		$row->load($id);
		
		$task = $this->getTask();

		if (!$row->bind(JRequest::get('post'))) {
			JError::raiseError(500, $row->getError());
		}
		
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();
	
		$msg = JText::sprintf('WF_MEDIABOX_SAVED');		
	
		switch ( $task )
		{
			case 'apply':
				$this->setRedirect( 'index.php?option=com_jce&view=mediabox', $msg );
				break;

			case 'save':
			default:
				$this->setRedirect( 'index.php?option=com_jce&view=cpanel', $msg );
				break;
		}
	}
}
?>