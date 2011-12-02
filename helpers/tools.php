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

abstract class WFToolsHelper {
	
	public static function getTemplates()
	{
		$db = JFactory::getDBO();
		
		// Joomla! 1.5
		if (WF_JOOMLA15) {		
			$query = 'SELECT template'
			. ' FROM #__templates_menu'
			. ' WHERE client_id = 0'
			;
		// Joomla! 1.6+
		} else {
			$query = 'SELECT template'
			. ' FROM #__template_styles'
			. ' WHERE client_id = 0'
			. ' AND home = 1'
			;
		}

		$db->setQuery($query);	
		return $db->loadResultArray();
	}
	
	public static function parseColors($file)
	{
		$data 	= '';
		$colors = array();	
		$file 	= realpath($file);	
			
		if ($file && is_file($file)) {
			$data = JFile::read($file);
		}	
		
		if ($data) {
			if (preg_match_all('/@import url\(([^\)]+)\)/', $data, $matches)) {
				$template = self::getTemplates();
				
				foreach ($matches[1] as $match) {
					$file = JPATH_SITE.DS.'templates'.DS.$template.DS.'css'.DS.$match;
					
					if ($file) {
						self::parseColors($file);
					}
				}
			}	
			preg_match_all('/#[0-9a-f]{3,6}/i', $data, $matches);
			
			$colors = $matches[0];
		}
	
		return $colors;
	}
	
	public static function getTemplateColors() 
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$colors 	= array();
		$path		= '';
		
		$templates 	= self::getTemplates();		
		
		foreach($templates as $template) {
			// Template CSS
        	$path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'css';
			// get the first path that exists
			if (is_dir($path)) {
				break;
			}
			// reset path
			$path = '';
		}
		
		if ($path) {
			$files = JFolder::files($path, '\.css$', false, true);
		
			foreach ($files as $file) {
				$colors = array_merge($colors, WFToolsHelper::parseColors($file));
			}
		}

		return implode(",", array_unique($colors));
	}
	public static function getOptions($params)
	{
		$options = array(
			'editableselects' 	=>	array('label' => WFText::_('WF_TOOLS_EDITABLESELECT_LABEL')),
			'extensions'		=>	array(
				'labels' 		=> array(
					'type_new'		=> WFText::_('WF_EXTENSION_MAPPER_TYPE_NEW'),
					'group_new'		=> WFText::_('WF_EXTENSION_MAPPER_GROUP_NEW'),
					'acrobat' 		=> WFText::_('WF_FILEGROUP_ACROBAT'),
					'office'    	=> WFText::_('WF_FILEGROUP_OFFICE'),
					'flash'			=> WFText::_('WF_FILEGROUP_FLASH'),
					'shockwave' 	=> WFText::_('WF_FILEGROUP_SHOCKWAVE'),
					'quicktime' 	=> WFText::_('WF_FILEGROUP_QUICKTIME'),
					'windowsmedia' 	=> WFText::_('WF_FILEGROUP_WINDOWSMEDIA'),
					'silverlight' 	=> WFText::_('WF_FILEGROUP_SILVERLIGHT'),
					'openoffice'    => WFText::_('WF_FILEGROUP_OPENOFFICE'),
					'divx'    		=> WFText::_('WF_FILEGROUP_DIVX'),
					'real'    		=> WFText::_('WF_FILEGROUP_REAL'),
					'video'    		=> WFText::_('WF_FILEGROUP_VIDEO'),
					'audio'    		=> WFText::_('WF_FILEGROUP_AUDIO')
				)
			),
			'colorpicker'	=> array(
				'template_colors' 	=> self::getTemplateColors(),
				'custom_colors' 	=> $params->get('editor.custom_colors'),
				'labels' => array(					
					'title'		=> WFText::_('WF_COLORPICKER_TITLE'),
					'picker'	=> WFText::_('WF_COLORPICKER_PICKER'),
					'palette'	=> WFText::_('WF_COLORPICKER_PALETTE'),
					'named'		=> WFText::_('WF_COLORPICKER_NAMED'),
					'template'	=> WFText::_('WF_COLORPICKER_TEMPLATE'),
					'custom'	=> WFText::_('WF_COLORPICKER_CUSTOM'),
					'color'		=> WFText::_('WF_COLORPICKER_COLOR'),
					'apply'		=> WFText::_('WF_COLORPICKER_APPLY'),
					'name'		=> WFText::_('WF_COLORPICKER_NAME')
				)
			),
			'browser' => array(
				'title' => WFText::_('WF_BROWSER_TITLE')	
			)
		);

		return $options;
	}
}
?>