<?php
/**
 * @version		$Id: toolbar.php 222 2011-06-11 17:32:06Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
class WFToolbarHelper {
	
	static function help( $type, $alt='Help' ){
		jimport('joomla.plugin.helper');
		
		$language 	= JFactory::getLanguage();
		$tag		= $language->getTag();
		
		$sub		= explode('.', $type);			
		$category 	= array_shift($sub);
		$article 	= implode('.', $sub);

		$link		= '&category='.$category.'&article='.$article;
	
		$bar 		= JToolBar::getInstance('toolbar');
		
		$options = array(
			'width' 	=> 760,
			'height'	=> 540,
			'modal'		=> true
		);
		
		$html 	= '<a href="index.php?option=com_jce&amp;view=help&amp;tmpl=component&amp;section=admin'. $link .'&amp;lang='.substr($tag, 0, strpos( $tag, '-')).'" target="_blank" data-options="' . str_replace('"', "'", json_encode($options)) . '" class="dialog help" title="'.WFText::_('WF_HELP').'">';
		$html  .= '<span class="icon-32-help" title="'.WFText::_('WF_HELP').'"></span>'.WFText::_('WF_HELP').'</a>';		 
		
		$bar->appendButton('Custom', $html, 'help');
	}
	
	/**
	* Writes a configuration button and invokes a cancel operation (eg a checkin)
	* @param	string	The name of the component, eg, com_content
	* @param	int		The height of the popup
	* @param	int		The width of the popup
	* @param	string	The name of the button
	* @param	string	An alternative path for the configuation xml relative to JPATH_SITE
	* @since 1.0
	*/
	static function preferences()
	{
		$bar = JToolBar::getInstance('toolbar');
		
		$options = array(
			'width' 	=> 760,
			'height'	=> 540,
			'modal'		=> true
		);
		
		$html 	= '<a href="index.php?option=com_jce&amp;view=preferences&amp;tmpl=component" target="_blank" data-options="' . str_replace('"', "'", json_encode($options)) . '" class="dialog preferences" title="'.WFText::_('WF_PREFERENCES_TITLE').'">';
		$html  .= '<span class="icon-32-config icon-32-options" title="'.WFText::_('WF_PREFERENCES_TITLE').'"></span>'.WFText::_('WF_PREFERENCES').'</a>';
		
		$bar->appendButton('Custom', $html, 'config');
	}
	
	/**
	* Writes a configuration button and invokes a cancel operation (eg a checkin)
	* @param	string	The name of the component, eg, com_content
	* @param	int		The height of the popup
	* @param	int		The width of the popup
	* @param	string	The name of the button
	* @param	string	An alternative path for the configuation xml relative to JPATH_SITE
	* @since 1.0
	*/
	static function updates($enabled = false)
	{
		$bar = JToolBar::getInstance('toolbar');
		// Add a configuration button
		$options = array(
			'width' 	=> 760,
			'height'	=> 540,
			'modal'		=> true
		);
		
		if ($enabled) {
			$html 	= '<a href="index.php?option=com_jce&amp;view=updates&amp;tmpl=component" target="_blank" data-options="' . str_replace('"', "'", json_encode($options)) . '" class="dialog updates" title="'.WFText::_('WF_UPDATES').'">';
			$html  .= '<span class="icon-32-default icon-32-update" title="'.WFText::_('WF_UPDATES_CHECK').'"></span>'.WFText::_('WF_UPDATES').'</a>';
		} else {
			$html  = '<a href="#"><span class="icon-32-default icon-32-update" title="'.WFText::_('WF_UPDATES_NOSUPPORT').'"><span class="icon-32-error"></span></span>'.WFText::_('WF_UPDATES_NOSUPPORT').'</a>';
		}

		$bar->appendButton('Custom', $html, 'config');
	}
	
	static function access() {
		$bar = JToolBar::getInstance('toolbar');
		
		$options = array(
			'width' 	=> 760,
			'height'	=> 540,
			'modal'		=> true,
			'buttons'	=> '{}'
		);
		
		$html 	= '<a href="index.php?option=com_config&amp;view=component&amp;component=com_jce&amp;path=&amp;tmpl=component" target="_blank" data-options="' . str_replace('"', "'", json_encode($options)) . '" class="dialog preferences" title="'.WFText::_('WF_PREFERENCES_TITLE').'">';
		$html  .= '<span class="icon-32-lock" title="'.WFText::_('WF_ACCESS_TITLE').'"></span>'.WFText::_('WF_ACCESS').'</a>';
		
		$bar->appendButton('Custom', $html, 'access');	
	}
	
	static function export()
	{
		$icon = WF_JOOMLA15 ? 'unarchive' : 'export';							
		self::custom('export', $icon . '.png', $icon . '_f2.png', WFText::_('WF_PROFILES_EXPORT'), true);
	}
	
	static function save($task = 'save')
	{
		return JToolBarHelper::save($task);
	}
	
	static function apply($task = 'apply')
	{
		return JToolbarHelper::apply($task);
	}
	
	static function cancel($task = 'cancel', $alt = 'Cancel')
	{
		return JToolbarHelper::cancel($task, $alt);
	}
	
	static function editListx($task = 'edit', $alt = 'Edit')
	{
		return JToolbarHelper::editListx($task, $alt);
	}
	
	static function addNewx($task = 'add', $alt = 'New')
	{
		return JToolbarHelper::addNewx($task, $alt);
	}
	
	static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $x = false)
	{
		return JToolbarHelper::custom($task, $icon, $iconOver, $alt, $listSelect, $x);
	}
	
	static function publishList($task = 'publish', $alt = 'Publish')
	{
		return JToolbarHelper::publishList($task, $alt);
	}
	
	static function unpublishList($task = 'unpublish', $alt = 'Unpublish')
	{
		return JToolbarHelper::unpublishList($task, $alt);
	}
	
	static function deleteList($msg = '', $task = 'remove', $alt = 'Delete')
	{
		return JToolbarHelper::deleteList($msg, $task, $alt);
	}
}
?>