<?php
/**
 * @version		$Id: preferences.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
class WFControllerPreferences extends WFController
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
	
	function filter($data)
	{
		$model 	= $this->getModel('preferences');
		$form 	= $model->getForm();
		
		if (is_a($form, 'JForm')) {
			return $form->filter($data);
		}
		
		return $data;
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'RESTRICTED' );

		$db = JFactory::getDBO();	

		$post = JRequest::getVar('params', '', 'POST', 'ARRAY');
		$registry = new JRegistry();
		$registry->loadArray($post);
		
		// get params
		$component 	= WFExtensionHelper::getComponent();
		// create params object from json string
		$params 	= json_decode($component->params);

		// set preferences object
		$preferences = $registry->toObject();	

		if (isset($preferences->rules)) {
			jimport('joomla.access.rules');
			
			if (class_exists('JRules')) {	
			
				$data 	= $this->filter($post);
			
				$rules	= new JRules($data['rules']);
				$asset	= JTable::getInstance('asset');
				
				$option = JRequest::getCmd('option');
	
				if (!$asset->loadByName($option)) {
					$root = JTable::getInstance('asset');
					$root->loadByName('root.1');
					$asset->name 	= $option;
					$asset->title 	= $option;
					$asset->setLocation($root->id,'last-child');
				}

				$asset->rules = (string) $rules;
	
				if (!$asset->check() || !$asset->store()) {
					JError::raiseError(500, $asset->getError());
					return false;
				}
			// Joomla! 1.5
			} else {		
				$params->access = $preferences->rules;
			}	
		}
		
		if (isset($preferences->preferences)) {
			$params->preferences = $preferences->preferences;

			// set params as JSON string
			$component->params = json_encode($params);
	
			if (!$component->check()) {
				JError::raiseError(500, $row->getError());
			}
			if (!$component->store()) {
				JError::raiseError(500, $row->getError());
			}
			$component->checkin();
		}
		
		$close = 0;
		
		if ($this->getTask() == 'save') {
			$close = 1;
		}

		$this->setRedirect('index.php?option=com_jce&view=preferences&tmpl=component&close=' . $close, WFText::_('WF_PREFERENCES_SAVED'));
	}
}
?>