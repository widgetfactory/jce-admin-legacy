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
 * Control Panel View
 *
 * @package		JCE
 * @since		1.6
 */
class WFViewCpanel extends JView
{
    function display($tpl = null)
    {
       	wfimport('admin.models.updates');	
			
       	$mainframe = JFactory::getApplication();

        $model =$this->getModel();
        $installer = WFInstaller::getInstance();
		
		$version = $model->getVersion();

        // Check Groups DB
        if (!$installer->profiles) {
            $link = JHTML::link('index.php?option=com_jce&amp;task=repair&amp;table=profiles', WFText::_('WF_DB_CREATE_RESTORE'));
            $mainframe->enqueueMessage(WFText::_('WF_DB_PROFILES_ERROR').' - '.$link, 'error');
        }
		
		$component = WFExtensionHelper::getComponent();        
        
        // get params definitions
        $params = new WFParameter($component->params, '', 'preferences');
		
		$canUpdate = WFModelUpdates::canUpdate();
        
        $options = array(
        	'feed'				=> (int)$params->get('feed', 0),
        	'updates'			=> (int)$params->get('updates', $canUpdate ? 1 : 0),
        	'labels'			=> array(
				'feed' 				=> WFText::_('WF_CPANEL_FEED_LOAD'),
	        	'updates'			=> WFText::_('WF_UPDATES'),
	        	'updates_available' => WFText::_('WF_UPDATES_AVAILABLE')
			)
        		
        );

        $this->document->addScript('components/com_jce/media/js/cpanel.js?version=' . $model->getVersion());
      
		$this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.CPanel.init('.json_encode($options).')});');
		
		WFToolbarHelper::preferences();
		WFToolbarHelper::updates($canUpdate);

		WFToolbarHelper::help( 'cpanel.about' );

        $this->assignRef('icons', $icons);
        $this->assignRef('model', $model);
        $this->assignRef('installer', $installer);
        $this->assignRef('params', $params);
        
        $this->assignRef('version', $version);
        
        parent::display($tpl);
    }
}
?>
