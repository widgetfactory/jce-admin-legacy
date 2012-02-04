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

class WFViewProfiles extends JView
{	
	function display($tpl = null)
    {
        $app = JFactory::getApplication();
        
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $acl = JFactory::getACL();
        
        $client = 'admin';
        
        $view   = JRequest::getWord('view');
        $task   = JRequest::getWord('task');
        $option = JRequest::getWord('option');
        
        $lists = array();
        
        $model = $this->getModel();
        
        switch ($task) {
            default:
            case 'publish':
            case 'unpublish':
            case 'remove':
            case 'save':
            case 'copy':
                $filter_order     	= $app->getUserStateFromRequest("$option.$view.filter_order", 'filter_order', 'p.ordering', 'cmd');
                $filter_order_Dir 	= $app->getUserStateFromRequest("$option.$view.filter_order_Dir", 'filter_order_Dir', '', 'word');
                $filter_state     	= $app->getUserStateFromRequest("$option.$view.filter_state", 'filter_state', '', 'word');
                $search           	= $app->getUserStateFromRequest("$option.$view.search", 'search', '', 'string');
                $search          	 = JString::strtolower($search);
                
                $limit				= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
				$limitstart 		= $app->getUserStateFromRequest("$option.$view.limitstart", 'limitstart', 0, 'int' );
                
                $where = array();
                
                if ($search) {
                    $where[] = 'LOWER( p.name ) LIKE ' . $db->Quote('%' . $db->getEscaped($search, true) . '%', false);
                }
                if ($filter_state) {
                    if ($filter_state == 'P') {
                        $where[] = 'p.published = 1';
                    } else if ($filter_state == 'U') {
                        $where[] = 'p.published = 0';
                    }
                }
                $where   = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
                $orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
                
                // get the total number of records
                $query = 'SELECT COUNT(p.id)' . ' FROM #__wf_profiles AS p' . $where;
                $db->setQuery($query);
                $total = $db->loadResult();
                
                jimport('joomla.html.pagination');
                $pagination = new JPagination($total, $limitstart, $limit);
                
                $query = 'SELECT p.*, u.name AS editor' 
                . ' FROM #__wf_profiles AS p' 
                . ' LEFT JOIN #__users AS u ON u.id = p.checked_out' 
                . $where 
                //. ' GROUP BY p.id' 
                . $orderby;
				
                $db->setQuery($query, $pagination->limitstart, $pagination->limit);
                $rows = $db->loadObjectList();
                if ($db->getErrorNum()) {
                    echo $db->stderr();
                    return false;
                }
                
                // table ordering
                $lists['order_Dir'] = $filter_order_Dir;
                $lists['order']     = $filter_order;
                
                // search filter
                $lists['search'] = $search;
                
                $this->assignRef('user', $user);
                $this->assignRef('lists', $lists);
                $this->assignRef('rows', $rows);
                $this->assignRef('pagination', $pagination);
                
                //JToolBarHelper::title(WFText::_('WF_PROFILES_TITLE').' : '.WFText::_('WF_PROFILES_LIST'), 'profiles.png' );
                
                WFToolbarHelper::editListX();
                WFToolbarHelper::addNewX();
                WFToolbarHelper::custom('copy', 'copy.png', 'copy_f2.png', WFText::_('WF_PROFILES_COPY'), true);
                WFToolbarHelper::export();
  
                if (count($rows) > 1) {
                    WFToolbarHelper::publishList();
                    WFToolbarHelper::unpublishList();
                    WFToolbarHelper::deleteList();
                }
                WFToolbarHelper::help('profiles.about');
                
                $options = array(					
                    'button' 	=> 'upload_button',
                    'task' 		=> 'import',
                    'labels' 	=> array(
                        'browse' => WFText::_('WF_LABEL_BROWSE'),
                        'alert' => WFText::_('WF_PROFILES_IMPORT_BROWSE_ERROR')
                    )
                );

                $this->document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/uploads.js?version=' . $model->getVersion());
                $this->document->addScriptDeclaration('jQuery(document).ready(function($){$(":file").upload(' . json_encode($options) . ')});');
                
                $this->setLayout('default');
                break;
            case 'apply':
            case 'add':
            case 'edit':
                // Load media   
                $scripts = array(
                    'profiles.js',
                    'extensions.js',
                    'checklist.js',
                	'parameter.js'
                );
                // Load scripts
                foreach ($scripts as $script) {
                    $this->document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/' . $script . '?version=' . $model->getVersion());
                }

				$this->document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/colorpicker.js?version=' . $model->getVersion());
				$this->document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/select.js?version=' . $model->getVersion());
                
                $cid = JRequest::getVar('cid', array(
                    0
                ), '', 'array');
                JArrayHelper::toInteger($cid, array(
                    0
                ));
                
                $lists = array();
                $row = JTable::getInstance('profiles', 'WFTable');
                
                // load the row from the db table
                $row->load($cid[0]);
                
                // fail if checked out not by 'me'
                
                if ($row->isCheckedOut($user->get('id'))) {
                    $msg = JText::sprintf('WF_PROFILES_CHECKED_OUT', $row->name);
                    $this->setRedirect('index.php?option=' . $option . '&view=profiles', $msg, 'error');
                    return false;
                }
                // Load editor params
                $component = JComponentHelper::getComponent('com_jce');
                
                // Load Language
                $language = JFactory::getLanguage();
                $language->load('com_jce', JPATH_SITE);
                
                $language->load('plg_editors_jce', JPATH_ADMINISTRATOR);                
                $plugins = $model->getPlugins();
                
                // load the row from the db table
                if ($cid[0]) {
                    $row->checkout($user->get('id'));
                } else {
                    $query = 'SELECT COUNT(id)' . ' FROM #__wf_profiles';
                    $db->setQuery($query);
                    $total = $db->loadResult();
                    
                    // get the defaults from xml
                    $row = $model->getDefaultProfile();
                    
                    if (!is_object($row)) {
                        $row->name        = '';
                        $row->description = '';
                        $row->types       = '';
                        $row->components  = '';
                        $row->area        = 0;
                        $row->types       = '';
                        $row->rows        = '';
                        $row->plugins     = '';
                        $row->published   = 1;
                        $row->ordering    = 0;
                        $row->params      = '{}';
                    }

                    $row->params = json_decode($row->params . ',' . $component->params);
                }
                
                $row->area = (isset($row->area)) ? $row->area : 0;
                
                // build the html select list for ordering
                $query              = 'SELECT ordering AS value, name AS text' . ' FROM #__wf_profiles' . ' WHERE published = 1' . ' AND ordering > -10000' . ' AND ordering < 10000' . ' ORDER BY ordering';
                $order              = JHTML::_('list.genericordering', $query);
                $lists['ordering']  = JHTML::_('select.genericlist', $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval($row->ordering));
                $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published);
                
                $exclude = array(
                    'com_admin',
                    'com_cache',
                    'com_jce',
                    'com_wrapper',
                    'com_search',
                    'com_user'
                );
                
                if (WF_JOOMLA15) {
                    $query = "SELECT *" . " FROM #__components" . " WHERE parent = 0" . " AND enabled = 1" . " ORDER BY name";
                } else {
                    $query = "SELECT *" . " FROM #__extensions" . " WHERE type = " . $db->Quote('component') . " AND client_id = 1 AND enabled = 1" . " ORDER BY name";
                }
                $db->setQuery($query);
                $components = $db->loadObjectList();
                
                $options = array();
                
                for ($i = 0; $i < count($components); $i++) {
                    if (isset($components[$i]->element)) {
                        $components[$i]->option = $components[$i]->element;
                    }
                    
                    if (!in_array($components[$i]->option, $exclude)) {
                        $options[] = JHTML::_('select.option', $components[$i]->option, WFText::_($components[$i]->name), 'value', 'text');
                    }
                }
                
                $disabled = (!$row->components) ? ' disabled="disabled"' : '';
                
                $lists['components'] = JHTML::_('select.genericlist', $options, 'components[]', 'class="inputbox levels" size="10" multiple="multiple"' . $disabled, 'value', 'text', explode(',', $row->components));
                
                $options   = array();
                $options[] = JHTML::_('select.option', 'all', WFText::_('WF_PROFILES_COMPONENTS_ALL'));
                $options[] = JHTML::_('select.option', 'select', WFText::_('WF_PROFILES_COMPONENTS_SELECT'));
                
                $lists['components-select'] = JHTML::_('select.radiolist', $options, 'components-select', 'class="inputbox"', 'value', 'text', $row->components ? 'select' : 'all', false);
                
                $options   = array();
                $options[] = JHTML::_('select.option', '', '-- ' . WFText::_('WF_PROFILES_AREA_SELECT') . ' --');
                $options[] = JHTML::_('select.option', 0, WFText::_('WF_PROFILES_AREA_BOTH'));
                $options[] = JHTML::_('select.option', 1, WFText::_('WF_PROFILES_AREA_FRONTEND'));
                $options[] = JHTML::_('select.option', 2, WFText::_('WF_PROFILES_AREA_BACKEND'));
                
                $lists['area'] = JHTML::_('select.genericlist', $options, 'area', 'class="inputbox levels" size="1"', 'value', 'text', $row->area);
                
                $query = 'SELECT types' . ' FROM #__wf_profiles'
                // Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
                    . ' WHERE id NOT IN (17,28,29,30)';
                $db->setQuery($query);
                $types = $db->loadResultArray();
                
                if (WF_JOOMLA15) {
                    // get list of Groups for dropdown filter
                    $query = 'SELECT id AS value, name AS text' . ' FROM #__core_acl_aro_groups'
                    // Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
                        . ' WHERE id NOT IN (17,28,29,30)';
                    $db->setQuery($query);
                    $types = $db->loadObjectList();
                    
                    $i       = '-';
                    $options = array(
                        JHTML::_('select.option', '0', WFText::_('Guest'))
                    );
                    
                    foreach ($types as $type) {
                        $options[] = JHTML::_('select.option', $type->value, $i . WFText::_($type->text));
                        $i .= '-';
                    }
                } else {
                    $options = array();
                    
                    $join  = ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt';
                    $where = '';
                    
                    $query = 'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level'
					. ' FROM #__usergroups AS a'
					. ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt'
					. ' GROUP BY a.id, a.title, a.lft, a.rgt'
					. ' ORDER BY a.lft ASC'
					;
                    
                    // Prevent parenting to children of this item.
                    
                    /*if ($id = $this->form->getValue('id')) {
                    $query->join('LEFT', '`#__usergroups` AS p ON p.id = '.(int) $id);
                    $query->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
                    }*/
                    
                    // Get the options.
                    $db->setQuery($query);
                    $options = $db->loadObjectList();
                    
                    // Pad the option text with spaces using depth level as a multiplier.
                    for ($i = 0, $n = count($options); $i < $n; $i++) {
                        $options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
                    }
                }
                
                $lists['types'] = JHTML::_('select.genericlist', $options, 'types[]', 'class="inputbox levels" size="8" multiple="multiple"', 'value', 'text', $row->types == '' ? '' : explode(',', $row->types));
                
                $options = array();
                
                if ($row->id && $row->users) {
                    $query = 'SELECT id as value, username as text' . ' FROM #__users' . ' WHERE id IN (' . $row->users . ')';
                    
                    $db->setQuery($query);
                    $gusers = $db->loadObjectList();
                    if ($gusers) {
                        foreach ($gusers as $guser) {
                            $options[] = JHTML::_('select.option', $guser->value, $guser->text);
                        }
                    }
                }
                $lists['users'] = JHTML::_('select.genericlist', $options, 'users[]', 'class="inputbox users" size="10" multiple="multiple"', 'value', 'text', '');
                
                // get params definitions
                $xml = WF_EDITOR_LIBRARIES . DS . 'xml' . DS . 'config' . DS . 'profiles.xml';
                
                // get editor params
                $params = new WFParameter($row->params, $xml, 'editor');               
                $params->addElementPath(JPATH_COMPONENT . DS . 'elements');
                $params->addElementPath(WF_EDITOR . DS . 'elements');
                
                // get width
                $width = $params->get('editor_width', 600);
                
                $groups = $params->getGroups();                
                $rows 	= $model->getRowArray($row->rows);
                
                $this->assign('width', 		$width);
                $this->assignRef('lists', 	$lists);
                $this->assignRef('profile', $row);
                $this->assignRef('rows', 	$rows);
                $this->assignRef('params', 	$params);
                $this->assignRef('groups', 	$groups);
                $this->assignRef('plugins', $plugins);
                
                $options = WFToolsHelper::getOptions($params);
                
                $this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Profiles.init(' . json_encode($options) . ')});');
                
                if ($row->id) {
					JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_PROFILES_EDIT') . ' - [' . $row->name . ']', 'logo.png');
                } else {
                	JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_PROFILES_NEW'), 'logo.png');
                }

                WFToolbarHelper::save();
                WFToolbarHelper::apply();
                WFToolbarHelper::cancel('cancelEdit', 'Close');
                WFToolbarHelper::help('profiles.edit');
                
                JRequest::setVar('hidemainmenu', 1);
                
                $this->setLayout('form');
                break;
        }
        $this->assignRef('model', $model);
        
        parent::display($tpl);
    }
}