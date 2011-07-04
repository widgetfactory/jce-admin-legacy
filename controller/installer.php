<?php
/**
 * @version		$Id: installer.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
class WFControllerInstaller extends WFController
{	
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{		
		parent::__construct();
		
		$this->registerTask( 'disable', 'enable' );
				
		$language = JFactory::getLanguage();		
		$language->load( 'com_installer', JPATH_ADMINISTRATOR );
	}
		
	function display()
	{		
		parent::display();
	}

	/**
	 * Install an extension
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function install()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'RESTRICTED' );
		
		$document 	= JFactory::getDocument();
		$view 		= $this->getView('installer', $document->getType());
		$model 		= $this->getModel('installer');

		if ($model->install()) {
			$cache =JFactory::getCache('mod_menu');
			$cache->clean();
		}
		
		$view->setModel($model, true);
		
		$method = JRequest::getWord('method');
		
		if ($method && $method == 'iframe') {
			$view->setLayout('install');
			exit($view->loadTemplate('message'));
		}
		
		$view->loadHelper('toolbar');
		$this->loadMenu();
		
		// load head override
		$app =JFactory::getApplication();
		$app->registerEvent('onAfterRender', 'WFSystemHelper');
		
		$view->assignRef('document', $document);	
		$view->display();
	}

	/**
	 * Remove (uninstall) an extension
	 *
	 * @static
	 * @param	array	An array of identifiers
	 * @return	boolean	True on success
	 * @since 1.0
	 */
	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'RESTRICTED' );

		$document 	= JFactory::getDocument();
		$view 		= $this->getView('installer', $document->getType());
		$model 		= $this->getModel('installer');
		
		$items = array(
			'plugin'		=>	JRequest::getVar('pid', array (), '', 'array'),
			'extension'		=>	JRequest::getVar('eid', array (), '', 'array'),
			'language'		=>	JRequest::getVar('lid', array (), '', 'array'),
			'related'		=>	JRequest::getVar('rid', array (), '', 'array')
		);
		
		// Uninstall the chosen extensions
		foreach ($items as $type => $ids) {
			if (count($ids)) {
				foreach ($ids as $id) {
					if ($id) {
						if ($model->remove($id, $type)) {
							$cache =JFactory::getCache('mod_menu');
							$cache->clean();
						}
					}
				}
			}
		}
		$view->loadHelper('toolbar');
		$this->loadMenu();
		
		// load head override
		$app =JFactory::getApplication();
		$app->registerEvent('onAfterRender', 'WFSystemHelper');
		
		$view->assignRef('document', $document);
		$view->setModel($model, true);		
		$view->display();
	}
}
?>