<?php 
/**
 * @version		$Id: view.html.php 231 2011-06-14 15:47:00Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('ERROR_403');

jimport('joomla.application.component.view');

/**
 * Legend View
 *
 * @package		JCE
 * @since		1.6
 */
class WFViewLegend extends JView
{
	function display($tpl = null)
	{
				
		jimport('joomla.filesystem.file');
				
		$db	= JFactory::getDBO();
    
    	$model = JModel::getInstance('plugins', 'WFModel');

    	$plugins  = $model->getPlugins();
    	$commands = $model->getCommands(); 

		$plugins = array_merge($commands, $plugins);
		
		$language = JFactory::getLanguage();
		$language->load('plg_editors_jce', JPATH_ADMINISTRATOR);

		$this->assignRef('plugins', $plugins);
		$this->assignRef('model', $model);
		$this->assignRef('language', $language);
		
		$this->document->addScript('components/com_jce/media/js/legend.js?version=' . $model->getVersion());
        $this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Legend.init();});');

		parent::display($tpl);
	}
}