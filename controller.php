<?php
/**
 * @version   $Id: controller.php 231 2011-06-14 15:47:00Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * JCE Component Controller
 *
 * @package JCE
 * @since   1.5.8
 */
class WFController extends JController
{
    /**
     * Custom Constructor
     */
    function __construct($default = array())
    {
        parent::__construct($default);
		
		$view = JRequest::getWord('view', 'cpanel');

        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');

        // load classes
        wfimport('admin.classes.installer');
        // load helpers
        wfimport('admin.helpers.parameter');
        wfimport('admin.helpers.extension');
        wfimport('admin.helpers.xml');

        $document = JFactory::getDocument();
        
        $document->setTitle(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_' . strtoupper($view)));
        
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);
		
		$model = $this->getModel($view);
        
        // jquery versions		
		$document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/jquery/jquery-' . WF_JQUERY . '.min.js?version=' . $model->getVersion());
		$document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/jquery/jquery-ui-' . WF_JQUERYUI . '.custom.min.js?version=' . $model->getVersion());
		
		// jQuery noConflict
        $document->addScriptDeclaration('jQuery.noConflict();');
		
		$scripts = array();
        
        switch ($view) {
            case 'help':
                $scripts[] = 'help.js';
                break;
            default:
                // load Joomla! core javascript
                if (method_exists('JHtml', 'core')) {
                	JHtml::core();	
				}
				
				require_once(JPATH_ADMINISTRATOR . DS . 'includes' . DS . 'toolbar.php');
                
                JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' &rsaquo;&rsaquo; ' . WFText::_('WF_' . strtoupper($view)), 'logo.png');

                $params = WFParameterHelper::getComponentParams();
                $theme  = $params->get('preferences.theme', 'jce');
                
                $scripts = array_merge(array(
                    'tips.js',
                    'html5.js'
                ));

				// Load admin scripts
				$document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/jce.js?version=' . $model->getVersion());
				
				$options = array(
					'labels' => array(
						'ok' 		=> WFText::_('WF_LABEL_OK'),
						'cancel' 	=> WFText::_('WF_LABEL_CANCEL'),
						'select'	=> WFText::_('WF_LABEL_SELECT'),
						'save'		=> WFText::_('WF_LABEL_SAVE'),
						'saveclose' => WFText::_('WF_LABEL_SAVECLOSE'),
						'alert'		=> WFText::_('WF_LABEL_ALERT'),
						'required'  => WFText::_('WF_MESSAGE_REQUIRED')
					)
				);
				
                $document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.init(' . json_encode($options) . ');});');
				
				$installer = WFInstaller::getInstance();
                $installer->check();
                
                break;
        }

		// Load site scripts
        foreach ($scripts as $script) {
        	$document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/' . $script . '?version=' . $model->getVersion());
        }
                           
		require_once(dirname(__FILE__) . DS . 'helpers' . DS . 'system.php');
                
        $app = JFactory::getApplication();
       	$app->registerEvent('onAfterRender', 'WFSystemHelper');
    }

    function loadMenu()
    {
        $view 	= JRequest::getWord('view', 'cpanel');
		$model 	= $this->getModel($view);
        
        JSubMenuHelper::addEntry(WFText::_('WF_CPANEL'), 'index.php?option=com_jce&view=cpanel', $view == 'cpanel');
        
        $subMenus = array(
            'WF_CONFIGURATION' 	=> 'config',
            'WF_PROFILES' 		=> 'profiles',
            'WF_INSTALL' 		=> 'installer'
        );
        
        if (JPluginHelper::isEnabled('system', 'jcemediabox')) {
            $subMenus['WF_MEDIABOX'] = 'mediabox';
        }
        
        foreach ($subMenus as $menu => $item) {        	
			if ($model->authorize($item)) {
				JSubMenuHelper::addEntry(WFText::_($menu), 'index.php?option=com_jce&view=' . $item, $view == $item);
			}
        }
    }
	
    /**
     * Display View
     * @return 
     */
    function display()
    {
        $document = JFactory::getDocument();
        $name = JRequest::getWord('view', 'cpanel');
        
        $view = $this->getView($name, $document->getType(), '', array(
            'base_path' => dirname(__FILE__)
        ));
        
        switch ($name) {	
            case 'help':
                if ($model = $this->getModel($name)) {
                    $view->setModel($model, true);
                }
                break;
            default:
                $view->addHelperPath(dirname(__FILE__) . DS . 'helpers');
                $this->addModelPath(dirname(__FILE__) . DS . 'models');
                 
                $view->loadHelper('toolbar');
                $view->loadHelper('tools');
                $view->loadHelper('xml');
                $view->loadHelper($name);
                
                if ($model = $this->getModel($name)) {
                    $view->setModel($model, true);
                }
                
                $this->loadMenu();

                break;
        }
        
        $view->assignRef('document', $document);
        $view->display();
    }
    
    /**
     * Generic cancel method
     * @return 
     */
    function cancel()
    {
        // Check for request forgeries
        JRequest::checkToken() or die('Invalid Token');
        $this->setRedirect(JRoute::_('index.php?option=com_jce&view=cpanel', false));
    }
    
    function repair()
    {
        $installer = WFInstaller::getInstance();   
        $installer->repair();
    }
	
	function authorize($task)
	{
		$view 	= JRequest::getWord('view', 'cpanel');	
		
		if ($view == 'popup') {
			return true;
		}
		
		$model 	= $this->getModel($view);
		
		if (!$model->authorize($task)) {
			$this->setRedirect('index.php', WFText::_('ALERTNOTAUTH'));
			return false;
		}
		
		return true;
	}
}
?>