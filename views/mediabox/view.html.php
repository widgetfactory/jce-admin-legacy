<?php 
/**
 * @version		$Id: view.html.php 234 2011-06-15 09:26:44Z happy_noodle_boy $
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
 * MediaBox View
 *
 * @package		JCE
 * @since		1.6
 */
class WFViewMediabox extends JView
{
	function getParams($data)
    {
    	// get params definitions
        $params = new JParameter($data);
        
    	if (WF_JOOMLA15) {
        	$xml = JPATH_PLUGINS.DS.'system'.DS.'jcemediabox.xml';
        	$params->loadSetupFile($xml);
        	
        	return $params->getParams();
        } else {
        	$xml = JPATH_PLUGINS.DS.'system'.DS.'jcemediabox'.DS.'jcemediabox.xml';
        	
        	$parser = JFactory::getXMLParser('Simple');

			if ($parser->loadFile($xml)) {
				if ($fieldsets = $parser->document->getElementByPath('config')->getElementByPath('fields')->children()) {
					foreach ($fieldsets as $fieldset) {
						$params->setXML($fieldset);
					}
				}
			}
			
			$groups = array();
			$array = array();

			foreach ($params->getGroups() as $group => $num) {
				$groups[] = $params->getParams('params', $group);
			}
			
			foreach($groups as $group) {
				$array = array_merge($array, $group);
			}
			
			return $array;
        }
    }
	
	function display($tpl = null)
    {
        $db = JFactory::getDBO();

        $lang = JFactory::getLanguage();
        $lang->load('plg_system_jcemediabox');

        $client = JRequest::getWord('client', 'site');
		$model = $this->getModel();
        
		$plugin = JPluginHelper::getPlugin('system', 'jcemediabox');
        
        $params = $this->getParams($plugin->params);
        
        $this->assignRef('params', $params);
        $this->assignRef('client', $client);
        
        $this->document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/colorpicker.js?version=' . $model->getVersion());
        $this->document->addStyleSheet('components/com_jce/media/css/colorpicker.css?version=' . $model->getVersion());
        
        $options = array(
			'template_colors' 	=> WFToolsHelper::getTemplateColors(),
			'custom_colors' 	=> '',
			'labels' => array(					
				'picker'	=> WFText::_('WF_COLORPICKER_PICKER'),
				'palette'	=> WFText::_('WF_COLORPICKER_PALETTE'),
				'named'		=> WFText::_('WF_COLORPICKER_NAMED'),
				'template'	=> WFText::_('WF_COLORPICKER_TEMPLATE'),
				'custom'	=> WFText::_('WF_COLORPICKER_CUSTOM'),
				'color'		=> WFText::_('WF_COLORPICKER_COLOR'),
				'apply'		=> WFText::_('WF_COLORPICKER_APPLY'),
				'name'		=> WFText::_('WF_COLORPICKER_NAME')
			)
        );

		$this->document->addScriptDeclaration('jQuery(document).ready(function($){$("input.color").colorpicker('.json_encode($options).');});');

		WFToolbarHelper::save();
		WFToolbarHelper::apply();
		WFToolbarHelper::help('mediabox.config');

        parent::display($tpl);
    }
}
