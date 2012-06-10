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

jimport( 'joomla.application.component.view');

class WFViewUsers extends JView
{
	function display($tpl = null)
	{
		$app 	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		
		$client = 'admin';
		$view 	= JRequest::getWord( 'view' );
		
		$db				= JFactory::getDBO();
		$currentUser	= JFactory::getUser();
		$acl			= JFactory::getACL();
		
		$model 			= $this->getModel();
		
		$this->document->addScript('components/com_jce/media/js/users.js?version=' . $model->getVersion());

		$filter_order		= $app->getUserStateFromRequest("$option.$view.filter_order",		'filter_order',		'a.name',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest("$option.$view.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$filter_type		= $app->getUserStateFromRequest("$option.$view.filter_type",		'filter_type', 		0,			'word' );
		$search				= $app->getUserStateFromRequest("$option.$view.search",				'search', 			'',			'cmd' );
		$search				= JString::strtolower( $search );

		$limit				= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart 		= $app->getUserStateFromRequest("$option.$view.limitstart", 'limitstart', 0, 'int' );

		$where = array();
		
		if (isset( $search ) && $search!= '')
		{
			$searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
			$where[] = 'a.username LIKE '.$searchEscaped.' OR a.email LIKE '.$searchEscaped.' OR a.name LIKE '.$searchEscaped;
		}
		
		$join = '';
		
		if (WF_JOOMLA15) {
			if ($filter_type) {
				$where[] = 'a.gid ='.(int)$filter_type;
			}
			// exclude any child group id's for this user
			$pgids = $acl->get_group_children( $currentUser->get('gid'), 'ARO', 'RECURSE' );
	
			if (is_array( $pgids ) && count( $pgids ) > 0) {
				JArrayHelper::toInteger($pgids);
				$where[] = 'a.gid NOT IN (' . implode( ',', $pgids ) . ')';
			}
			
			// Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
			$where[] = 'a.gid NOT IN (17,28,29,30)';
		} else {
			if ($filter_type) {
				$where[] = 'map.group_id = LOWER('.$db->Quote($filter_type).') ';
			}
		}

		// Only unblocked users
		$where[] = 'a.block = 0';
		
		$orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );
		
		jimport('joomla.html.pagination');

		if (WF_JOOMLA15) {
			$query = 'SELECT COUNT(a.id)'
			. ' FROM #__users AS a'
			. $where
			;
			$db->setQuery($query);
			$total = $db->loadResult();
			$pagination = new JPagination($total, $limitstart, $limit);
			
			$query = 'SELECT a.id, a.name, a.username, g.name AS groupname'
			. ' FROM #__users AS a'
			. ' INNER JOIN #__core_acl_aro AS aro ON aro.value = a.id'
			. ' INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id'
			. ' INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id'
			. $where
			. ' GROUP BY a.id, a.name, a.username, g.name'
			. $orderby
			;
		} else {
			// Join over the group mapping table.
			$query = 'SELECT COUNT(a.id)'
			. ' FROM #__users AS a'
			. ' LEFT JOIN #__user_usergroup_map AS map ON map.user_id = a.id'
			. $where
			;
			$db->setQuery($query);
			$total = $db->loadResult();
			
			$pagination = new JPagination($total, $limitstart, $limit);

			$query = 'SELECT a.id, a.name, a.username, g.title AS groupname'
			. ' FROM #__users AS a'
			. ' LEFT JOIN #__user_usergroup_map AS map ON map.user_id = a.id'
			. ' LEFT JOIN #__usergroups AS g ON g.id = map.group_id'
			. $where
			. ' GROUP BY a.id, a.name, a.username, g.title'
			. $orderby
			;
		}
		
		$db->setQuery($query, $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

		$options = array(
			JHTML::_('select.option',  '0', '- '. WFText::_('WF_USERS_GROUP_SELECT') .' -' )
		);

		if (WF_JOOMLA15) {
			// get list of Groups for dropdown filter
			$query = 'SELECT id AS value, name AS text'
			. ' FROM #__core_acl_aro_groups'
			// Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
			. ' WHERE id NOT IN (17,28,29,30)'
			;
			$db->setQuery($query);
			$types = $db->loadObjectList();
			
			$i = '-';
			
			foreach( $types as $type ){
				$options[] = JHTML::_('select.option', $type->value, $i . WFText::_( $type->text ) );
				$i .= '-';
			}
		} else {
			$join 	= ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt';
			$where 	= '';
			
			$query = 'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level'
			. ' FROM #__usergroups AS a'
			. ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt'
			. ' GROUP BY a.id, a.title, a.lft, a.rgt'
			. ' ORDER BY a.lft ASC'
			;
	
			// Get the options.
			$db->setQuery($query);		
			$items = $db->loadObjectList();

			// Pad the option text with spaces using depth level as a multiplier.
			for ($i = 0, $n = count($items); $i < $n; $i++) {
				$options[] = JHTML::_('select.option', $items[$i]->value, str_repeat('- ',$items[$i]->level).$items[$i]->text);
			}
		}

		$lists['group'] = JHTML::_('select.genericlist', $options, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type" );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$rows);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}