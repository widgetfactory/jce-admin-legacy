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
jimport('joomla.client.helper');

/**
 * Installer View
 *
 * @package		JCE
 * @since		1.6
 */
class WFViewInstaller extends JView
{
	function display($tpl=null)
	{
		wfimport('admin.models.updates');		
			
		$mainframe = JFactory::getApplication();
				
		$model 	= $this->getModel();
		$state	= $this->get('State');
		
		$layout = JRequest::getWord('layout', 'install');
		// Set toolbar items for the page
		//JToolBarHelper::title(WFText::_('WF_INSTALLER_TITLE').' : '.WFText::_('WF_INSTALLER_'.$layout), 'install.png');

		// Are there messages to display ?
		$showMessage	= false;
				
		$plugins 	= '';
		$extensions = '';
		$languages 	= '';

		WFToolbarHelper::updates(WFModelUpdates::canUpdate());
		WFToolbarHelper::help( 'installer.about' );
		
		$options = array(
			'extensions' 	=> array('zip','tar','gz','gzip','tgz','tbz2','bz2','bzip2'),
			'width'			=> 300,
			'button'		=> 'install_button',
			'task'			=> 'install',
			'iframe'		=> false,
			'labels'		=> array(
				'browse'	=> WFText::_('WF_LABEL_BROWSE'),
				'alert'		=> WFText::_('WF_INSTALLER_FILETYPE_ERROR')	
			)
		);
		$this->document->addScript('components/com_jce/media/js/installer.js?version=' . $model->getVersion());
		$this->document->addScript('components/com_jce/media/js/uploads.js?version=' . $model->getVersion());
		$this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Installer.init({});$(":file").upload('.json_encode($options).')});');
		
		$state->set('install.directory', $mainframe->getCfg('config.tmp_path'));
		
		$plugins 	= $model->getPlugins();
		$extensions = $model->getExtensions();
		$languages	= $model->getLanguages();
		$related	= $model->getRelated();
		//$discover	= $model->findPlugins();

		//$this->assignRef('discover',	$discover);
		$this->assignRef('plugins',		$plugins);
		$this->assignRef('extensions',	$extensions);
		$this->assignRef('languages',	$languages);
		$this->assignRef('related',		$related);
		
		$result = $state->get('result');

		$this->assign('showMessage',	count($result));
		$this->assignRef('model',		$model);
		$this->assignRef('state',		$state);
		
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		
		$this->assignRef('ftp', $ftp);
		
		$this->setLayout($layout);
		
		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item = $this->items[$index];
		$item->index	= $index;

		$item->cbd		= null;
		$item->style	= null;

		$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;

		$this->assignRef('item', $item);
	}
}