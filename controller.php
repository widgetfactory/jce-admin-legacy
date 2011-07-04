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

        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');

        // load classes
        wfimport('admin.classes.installer');
        // load helpers
        wfimport('admin.helpers.parameter');
        wfimport('admin.helpers.extension');
        wfimport('admin.helpers.xml');
        
        $view = JRequest::getWord('view', 'cpanel');       
        
        $document = JFactory::getDocument();
        
        $document->setTitle(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_' . strtoupper($view)));
        
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);
		
		$model = $this->getModel($view);
        
        // jquery versions
        $jquery = array('jquery/jquery-' . WF_JQUERY . '.min.js', 'jquery/jquery-ui-' . WF_JQUERYUI . '.custom.min.js');
        
        switch ($view) {
        	case 'popup' :
                break;
            case 'help':
                $scripts = array_merge($jquery, array(
                    'help.js'
                ));
                // Load scripts
                foreach ($scripts as $script) {
                    $document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/' . $script . '?version=' . $model->getVersion());
                }
                
                $document->addScriptDeclaration('jQuery.noConflict();');
                
		        require_once(dirname(__FILE__) . DS . 'helpers' . DS . 'system.php');
                
                $app = JFactory::getApplication();
                $app->registerEvent('onAfterRender', 'WFSystemHelper');
                
                break;
            default:
                // load Joomla! core javascript
                if (method_exists('JHtml', 'core')) {
                	JHtml::core();	
				}
                
                JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' &rsaquo;&rsaquo; ' . WFText::_('WF_' . strtoupper($view)), 'logo.png');

                $params = WFParameterHelper::getComponentParams();
                $theme  = $params->get('preferences.theme', 'jce');
                
                $scripts = array_merge($jquery, array(
                    'tips.js',
                    'html5.js',
                    'jce.js'
                ));
                // Load scripts
                foreach ($scripts as $script) {
                    $document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/' . $script . '?version=' . $model->getVersion());
                }
                
                $document->addScriptDeclaration('jQuery.noConflict();');
				
				$options = array(
					'labels' => array(
						'ok' 		=> WFText::_('WF_LABEL_OK'),
						'cancel' 	=> WFText::_('WF_LABEL_CANCEL'),
						'select'	=> WFText::_('WF_LABEL_SELECT'),
						'save'		=> WFText::_('WF_LABEL_SAVE'),
						'saveclose' => WFText::_('WF_LABEL_SAVECLOSE')
					)
				);
				
                $document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.init(' . json_encode($options) . ');});');
                
                require_once(dirname(__FILE__) . DS . 'helpers' . DS . 'system.php');
                
                $app = JFactory::getApplication();
                $app->registerEvent('onAfterRender', 'WFSystemHelper');
				
				$installer = WFInstaller::getInstance();
                $installer->check();
                
                break;
        }
    }

    function loadMenu()
    {
        $view = JRequest::getWord('view', 'cpanel');
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
            JSubMenuHelper::addEntry(WFText::_($menu), 'index.php?option=com_jce&view=' . $item, $view == $item);
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
            case 'popup':
                break;
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
		$user = JFactory::getUser();
		
		// Joomla! 1.5
		if (isset($user->gid)) {
			// get rules from parameters
			$component 	= JComponentHelper::getComponent('com_jce');
			$params 	= new WFParameter($component->params);
			
			if (isset($params->access)) {
				$rules 	= $params->access;
				$rule 	= 'core.' . $task;
				if (isset($rules->$rule) && is_object($rules->$rule)) {
					$gid = $user->$gid;	
					if (isset($rules->$rule->$gid) && $rules->$rule->$gid == 0) {
						$this->setRedirect('index.php', WFText::_('ALERTNOTAUTH'));	
					}	
				}
			}	
		} else {
			if (!$user->authorise('core.' . $task, 'com_jce')) {
				$this->setRedirect('index.php', WFText::_('ALERTNOTAUTH'));
			}
		}
		
		return true;
	}
}
?>