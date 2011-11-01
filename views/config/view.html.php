<?php
/**
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');

class WFViewConfig extends JView
{
    function display($tpl = null)
    {        
        $db =JFactory::getDBO();
        
        $language =JFactory::getLanguage();
        $language->load('plg_editors_jce', JPATH_ADMINISTRATOR);
        
        $client = JRequest::getWord('client', 'site');

        $model =$this->getModel();
        
        $lists = array();
        
        $component 	= WFExtensionHelper::getComponent();        
        $xml 		= WF_EDITOR_LIBRARIES.DS.'xml'.DS.'config'.DS.'editor.xml';
        
        // get params definitions
        $params = new WFParameter($component->params, $xml, 'editor');      
        $params->addElementPath(JPATH_COMPONENT.DS.'elements');
        
        $this->assignRef('model', 	$model);
        $this->assignRef('params', 	$params);
        $this->assignRef('client', 	$client);

        WFToolbarHelper::save();
        WFToolbarHelper::apply();
        WFToolbarHelper::help('config.about');
        
        parent::display($tpl);
    }
}
