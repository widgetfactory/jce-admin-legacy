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

class WFViewHelp extends JView
{
    function display($tpl = null)
    {        
        $model 		=$this->getModel();
        $language 	= $model->getLanguage();
        $lang 		= JFactory::getLanguage();
        
        $section 	= JRequest::getWord('section');
        $category 	= JRequest::getWord('category');
		$article 	= JRequest::getWord('article');
		
		$component 	= JComponentHelper::getComponent('com_jce');
		
		require_once(WF_ADMINISTRATOR . '/classes/parameter.php');
		
		$params 	= new WFParameter($component->params);
        $url  		= $params->get('preferences.help.url', 'http://www.joomlacontenteditor.net');
		$method 	= $params->get('preferences.help.method', 'reference');
		$pattern	= $params->get('preferences.help.pattern', '');

		switch ($method) {
			default:
			case 'reference':
				$url .= '/index.php?option=com_content&view=article&tmpl=component&print=1&mode=inline&task=findkey&lang='.$language.'&keyref=';
				break;
			case 'xml':
				break;
			case 'sef':
				break;
		}

        $this->assignRef('model', $model);
        $this->assign('orientation', $lang->isRTL() ? 'east' : 'west'); 
        
        $key = array();
        
        if ($section) {
        	$key[] = $section;
        	if ($category) {
        		$key[] = $category;
        		if ($article) {
        			$key[] = $article;
        		}
        	}
        }
		
		$options = array(
			'url'		=> $url,
			'key'		=> $key,
			'pattern' 	=> $pattern
		);
		
		$this->document->addStyleSheet(JURI::root(true) . '/components/com_jce/editor/libraries/css/help.css');
		
		$this->document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/jquery/jquery-ui-layout.js?version=' . $model->getVersion());
		$this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Help.init('.json_encode($options).');});');

        parent::display($tpl);
    }
}
