<?php
/**
 * @version		$Id: preferences.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// load base model
require_once(dirname(__FILE__) . DS . 'model.php');

class WFModelPreferences extends WFModel {

	/**
	 * Get Access Rules
	 */
	public function getAccessRules()
	{
		// Initialise some field attributes.
		$section 	= 'component';
		$component 	= 'com_jce';
		
		// Build the form control.
		$curLevel = 0;
		
		// load access classes
		jimport('joomla.access.access');
		
		if (class_exists('JAccess')) {
			// Get the actions for the asset.
			$actions = JAccess::getActions($component, $section);	
			
			// Get the explicit rules for this asset.
			// Need to find the asset id by the name of the component.
			$db = JFactory::getDbo();
			$db->setQuery('SELECT id FROM #__assets WHERE name = ' . $db->quote($component));
			$assetId = (int) $db->loadResult();
	
			if ($error = $db->getErrorMsg()) {
				JError::raiseNotice(500, $error);
			}
	
			// Get the rules for just this asset (non-recursive).
			$assetRules = JAccess::getAssetRules($assetId);
		} else {
			$actions = array();	
		}

		// Get actions from access.xml
		self::_getActions($actions);
		
		// get User Groups
		$groups = $this->getUserGroups();	
			
		$html = array();	
			
		$html[] = '<p class="rule-desc">' . JText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';
		$html[] = '<ul id="rules">';

		// Start a row for each user group.
		foreach ($groups as $group)
		{
			$difLevel = $group->level - $curLevel;

			if ($difLevel > 0) {
				$html[] = '<li><ul>';
			}
			else if ($difLevel < 0) {
				$html[] = str_repeat('</ul></li>', -$difLevel);
			}

			$html[] = '<li>';
			$html[] =	'<h3><a href="#"><span>';
			$html[] =	str_repeat('<span class="level"> &rsaquo; </span> ', $curLevel = $group->level) . $group->text;
			$html[] =	'</span></a></h3>';
			$html[] =	'<div>';
			$html[] =			'<table class="group-rules">';
			$html[] =				'<thead>';
			$html[] =					'<tr>';

			$html[] =						'<th class="actions" id="actions-th' . $group->value . '">';
			$html[] =							'<span class="acl-action">' . JText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] =						'</th>';

			$html[] =						'<th class="settings" id="settings-th' . $group->value . '">';
			$html[] =							'<span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] =						'</th>';

			// The calculated setting is not shown for the root group of global configuration.
			$canCalculateSettings = ($group->parent_id || !empty($component));
			
			if ($canCalculateSettings) {
				$html[] =					'<th id="aclactionth' . $group->value . '">';
				$html[] =						'<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] =					'</th>';
			}

			$html[] =					'</tr>';
			$html[] =				'</thead>';
			$html[] =				'<tbody>';

			foreach ($actions as $action)
			{
				$html[] =				'<tr>';
				$html[] =					'<td headers="actions-th' . $group->value . '">';
				$html[] =						'<label class="hasTip" for="' . $action->name . '_' . $group->value . '" title="'.htmlspecialchars(JText::_($action->title).'::'.JText::_($action->description), ENT_COMPAT, 'UTF-8').'">';
				$html[] =						JText::_($action->title);
				$html[] =						'</label>';
				$html[] =					'</td>';

				$html[] =					'<td headers="settings-th' . $group->value . '">';

				$html[] = '<select name="params[rules][' . $action->name . '][' . $group->value . ']" id="' . $action->name . '_' . $group->value . '" title="' . JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

				if (class_exists('JAccess')) {
					$inheritedRule	= JAccess::checkGroup($group->value, $action->name, $assetId);

					// Get the actual setting for the action for this group.
					$assetRule		= $assetRules->allow($action->name, $group->value);
					
					$coreAdmin 		= JAccess::checkGroup($group->value, 'core.admin');
					
				} else {
					$inheritedRule = false;
					$assetRule = false;	
					$coreAdmin = false;
				}

				// Build the dropdowns for the permissions sliders

				// The parent group has "Not Set", all children can rightly "Inherit" from that.
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>' .
							JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' .
							JText::_('JLIB_RULES_ALLOWED') . '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' .
							JText::_('JLIB_RULES_DENIED') . '</option>';

				$html[] = '</select>&#160; ';

				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false)) {
					$html[] = JText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				if ($canCalculateSettings) {
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					// This is where we show the current effective settings considering currrent group, path and cascade.
					// Check whether this is a component or global. Change the text slightly.

					if ($coreAdmin !== true)
					{
						if ($inheritedRule === null) {
							$html[] = '<span class="icon-16-unset">'.
										JText::_('JLIB_RULES_NOT_ALLOWED').'</span>';
						}
						else if ($inheritedRule === true)
						{
							$html[] = '<span class="icon-16-allowed">'.
										JText::_('JLIB_RULES_ALLOWED').'</span>';
						}
						else if ($inheritedRule === false) {
							if ($assetRule === false) {
								$html[] = '<span class="icon-16-denied">'.
											JText::_('JLIB_RULES_NOT_ALLOWED').'</span>';
							}
							else {
								$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">'.
											JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED').'</span></span>';
							}
						}

						//Now handle the groups with core.admin who always inherit an allow.
					}
					else if (!empty($component)) {
						$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">'.
									JText::_('JLIB_RULES_ALLOWED_ADMIN').'</span></span>';
					}
					else {
						
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table>';

			$html[] = '</div>';
			$html[] = '</li>';

		} // endforeach

		$html[] = str_repeat('</ul></li>', $curLevel);
		$html[] = '</ul><div class="rule-notes">';
		if ($section == 'component' || $section == null ) {
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
		} else {
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}
		$html[] = '</div>';

		return implode("\n", $html);	
	}

	/**
	 * Get Actions from access.xml file
	 */
	protected function _getActions(&$actions)
	{
		$file 	= JPATH_COMPONENT_ADMINISTRATOR . DS . 'access.xml';			
		$xml 	= WFXMLElement::getXML($file);
		
		if ($xml) {
			// Iterate over the children and add to the actions.
			foreach ($xml->section->children() as $element)
			{
				if ($element->name() == 'action') {
					$actions[] = (object) array(
						'name'			=> (string) $element['name'],
						'title'			=> (string) $element['title'],
						'description'	=> (string) $element['description']
					);
				}
			}
		}
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @return	array
	 * @since	1.6
	 */
	protected function getUserGroups()
	{
		$db = JFactory::getDBO();	
		
		$table = WF_JOOMLA15 ? '#__core_acl_aro_groups' : '#__usergroups';
		$where = WF_JOOMLA15 ? ' WHERE a.id IN (23,24,25) AND b.id IN (23,24,25)' : '';
		$title = WF_JOOMLA15 ? 'name' : 'title';
		
		
		// Initialise variables.
		$db		= JFactory::getDBO();
		$query = 'SELECT a.id AS value, a.' . $title . ' AS text, COUNT(DISTINCT b.id) AS level, a.parent_id'
		. ' FROM ' . $table . ' AS a'
		. ' LEFT JOIN ' . $table . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt'
		. $where
		. ' GROUP BY a.id'
		. ' ORDER BY a.lft ASC'
		;
	
		// Get the options.
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}