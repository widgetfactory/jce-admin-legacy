<?php
/**
 * @version		$Id: popup.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined( '_JEXEC' ) or die( 'RESTRICTED' );

/**
 * Users Component Controller
 *
 * @package		Joomla
 * @subpackage	Users
 * @since 1.5
 */
class WFControllerPopup extends JController
{
	/**
	 * Constructor
	 *
	 * @params	array	Controller configuration array
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Displays a view
	 */
	function display()
	{		
		$document = JFactory::getDocument();
		
		$this->addViewPath(JPATH_COMPONENT . DS . 'views');

		$view = $this->getView('popup', $document->getType());
		
		$view->assignRef('document', $document);
        $view->display();
	}
}