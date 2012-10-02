<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2012 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

jimport('joomla.application.component.view');

class WFViewProfiles extends JView {

    function display($tpl = null) {
        $app = JFactory::getApplication();

        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $acl = JFactory::getACL();

        $client = 'admin';

        $view = JRequest::getWord('view');
        $task = JRequest::getWord('task');
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
                $filter_order = $app->getUserStateFromRequest("$option.$view.filter_order", 'filter_order', 'p.ordering', 'cmd');
                $filter_order_Dir = $app->getUserStateFromRequest("$option.$view.filter_order_Dir", 'filter_order_Dir', '', 'word');
                $filter_state = $app->getUserStateFromRequest("$option.$view.filter_state", 'filter_state', '', 'word');
                $search = $app->getUserStateFromRequest("$option.$view.search", 'search', '', 'cmd');
                $search = JString::strtolower($search);

                $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
                $limitstart = $app->getUserStateFromRequest("$option.$view.limitstart", 'limitstart', 0, 'int');

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
                $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
                $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;

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
                $lists['order'] = $filter_order;

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
                    'button' => 'upload_button',
                    'task' => 'import',
                    'labels' => array(
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
                $language->load('com_jce', JPATH_ADMINISTRATOR);
                $language->load('com_jce', JPATH_SITE);

                $language->load('plg_editors_jce', JPATH_ADMINISTRATOR);
                $plugins = $model->getPlugins();

                // load plugin languages
                foreach ($plugins as $plugin) {
                    if ($plugin->core == 0) {
                        // Load Language for plugin
                        $language->load('com_jce_' . $plugin->name, JPATH_SITE);
                    }
                }

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
                        $row->name = '';
                        $row->description = '';
                        $row->types = '';
                        $row->components = '';
                        $row->area = 0;
                        $row->types = '';
                        $row->rows = '';
                        $row->plugins = '';
                        $row->published = 1;
                        $row->ordering = 0;
                        $row->params = '{}';
                    }

                    $row->params = json_decode($row->params . ',' . $component->params);
                }

                $row->area = (isset($row->area)) ? $row->area : 0;

                // build the html select list for ordering
                $query = 'SELECT ordering AS value, name AS text'
                        . ' FROM #__wf_profiles'
                        . ' WHERE published = 1'
                        . ' AND ordering > -10000'
                        . ' AND ordering < 10000'
                        . ' ORDER BY ordering';

                $order = JHTML::_('list.genericordering', $query);
                $lists['ordering'] = JHTML::_('select.genericlist', $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval($row->ordering));
                $lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published);

                $exclude = array(
                    'com_admin',
                    'com_cache',
                    'com_checkin',
                    'com_config',
                    'com_cpanel',
                    'com_finder',
                    'com_installer',
                    'com_languages',
                    'com_jce',
                    'com_login',
                    'com_menus',
                    'com_media',
                    'com_messages',
                    'com_newsfeeds',
                    'com_plugins',
                    'com_redirect',
                    'com_templates',
                    'com_users',
                    'com_wrapper',
                    'com_search',
                    'com_user',
                    'com_updates'
                );

                $query = $db->getQuery(true);

                if (is_object($query)) {
                    $query->select('element AS value, name AS text')->from('#__extensions')->where(array('type = ' . $db->Quote('component'), 'client_id = 1', 'enabled = 1'))->order('name');
                } else {
                    $query = "SELECT `option` AS value, name AS text"
                            . " FROM #__components"
                            . " WHERE parent = 0"
                            . " AND enabled = 1"
                            . " ORDER BY name";
                }

                $db->setQuery($query);
                $components = $db->loadObjectList();

                $options = array();
                
                // load component languages
                for ($i = 0; $i < count($components); $i++) {
                    if (!in_array($components[$i]->value, $exclude)) {
                        $options[] = $components[$i];
                        // load system language file
                        $language->load($components[$i]->value . '.sys', JPATH_ADMINISTRATOR);
                    }
                }
                // set disabled attribute
                $disabled = (!$row->components) ? ' disabled="disabled"' : '';

                // components list
                $lists['components'] = '<ul id="components" class="checkbox-list">';

                foreach ($options as $option) {
                    $checked = in_array($option->value, explode(',', $row->components)) ? ' checked="checked"' : '';
                    $lists['components'] .= '<li><input type="checkbox" name="components[]" value="' . $option->value . '"' . $checked . $disabled . ' /><label>' . JText::_($option->text) . '</label></li>';
                }

                $lists['components'] .= '</ul>';

                // components select
                $options = array();
                $options[] = JHTML::_('select.option', 'all', WFText::_('WF_PROFILES_COMPONENTS_ALL'));
                $options[] = JHTML::_('select.option', 'select', WFText::_('WF_PROFILES_COMPONENTS_SELECT'));

                $lists['components-select'] = JHTML::_('select.radiolist', $options, 'components-select', 'class="inputbox"', 'value', 'text', $row->components ? 'select' : 'all', false);

                // area
                $options = array();
                $options[] = JHTML::_('select.option', '', '-- ' . WFText::_('WF_PROFILES_AREA_SELECT') . ' --');
                $options[] = JHTML::_('select.option', 0, WFText::_('WF_PROFILES_AREA_BOTH'));
                $options[] = JHTML::_('select.option', 1, WFText::_('WF_PROFILES_AREA_FRONTEND'));
                $options[] = JHTML::_('select.option', 2, WFText::_('WF_PROFILES_AREA_BACKEND'));

                $lists['area'] = JHTML::_('select.genericlist', $options, 'area', 'class="inputbox levels" size="1"', 'value', 'text', $row->area);

                // user types from profile
                $query = $db->getQuery(true);

                if (is_object($query)) {
                    $query->select('types')->from('#__wf_profiles')->where('id NOT IN (17,28,29,30)');
                } else {
                    $query = 'SELECT types'
                            . ' FROM #__wf_profiles'
                            // Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
                            . ' WHERE id NOT IN (17,28,29,30)';
                }

                $db->setQuery($query);
                $types = $db->loadResultArray();

                if (JPATH_PLATFORM) {
                    $options = array();
                    
                    $query = $db->getQuery(true);

                    $query->select('a.id AS value, a.title AS text')->from('#__usergroups AS a');

                    // Add the level in the tree.
                    $query->select('COUNT(DISTINCT b.id) AS level');
                    $query->join('LEFT OUTER', '#__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt');
                    $query->group('a.id, a.lft, a.rgt, a.parent_id, a.title');
                    $query->order('a.lft ASC');

                    // Get the options.
                    $db->setQuery($query);
                    $options = $db->loadObjectList() or die($db->stdErr());

                    // Pad the option text with spaces using depth level as a multiplier.
                    for ($i = 0, $n = count($options); $i < $n; $i++) {
                        $options[$i]->text = str_repeat('<span class="gi">|&mdash;</span>', $options[$i]->level) . $options[$i]->text;
                    }
                } else {
                    // get list of Groups for dropdown filter
                    $query = 'SELECT id AS value, name AS text' 
                    . ' FROM #__core_acl_aro_groups'
                    // Exclude ROOT, USERS, Super Administrator, Public Frontend, Public Backend
                    . ' WHERE id NOT IN (17,28,29,30)';
                    $db->setQuery($query);
                    $types = $db->loadObjectList();

                    $i = '-';
                    $options = array(
                        JHTML::_('select.option', '0', WFText::_('Guest'))
                    );

                    foreach ($types as $type) {
                        $options[] = JHTML::_('select.option', $type->value, $i . WFText::_($type->text));
                        $i .= '|&mdash;';
                    }
                }

                $lists['usergroups'] = '<ul id="user-groups" class="checkbox-list">';

                foreach ($options as $option) {
                    $checked = in_array($option->value, explode(',', $row->types)) ? ' checked="checked"' : '';
                    $lists['usergroups'] .= '<li><input type="checkbox" name="usergroups[]" value="' . $option->value . '"' . $checked . ' /><label>' . $option->text . '</label></li>';
                }

                $lists['usergroups'] .= '</ul>';

                // users
                $options = array();

                if ($row->id && $row->users) {
                    $query = $db->getQuery(true);

                    if (is_object($query)) {
                        $query->select('id AS value, username AS text')->from('#__users')->where('id IN (' . $row->users . ')');
                    } else {
                        $query = 'SELECT id as value, username as text'
                        . ' FROM #__users'
                        . ' WHERE id IN (' . $row->users . ')';
                    }

                    $db->setQuery($query);
                    $gusers = $db->loadObjectList();

                    if ($gusers) {
                        foreach ($gusers as $guser) {
                            $options[] = JHTML::_('select.option', $guser->value, $guser->text);
                        }
                    }
                }
                $lists['users'] = '<ul id="users" class="users-list">';

                foreach ($options as $option) {
                    $lists['users'] .= '<li><input type="hidden" name="users[]" value="' . $option->value . '" /><label><span class="users-list-delete"></span>' . $option->text . '</label></li>';
                }

                $lists['users'] .= '</ul>';

                // Get layout rows
                $rows = $model->getRowArray($row->rows);

                // assign params to row
                $model->getEditorParams($row);
                $model->getLayoutParams($row);

                // create $params object for "editor"
                $params = new WFParameter($row->params, '', 'editor');

                // load other theme css
                foreach ($model->getThemes() as $theme) {
                    $files = JFolder::files($theme, 'ui([\w\.]*)\.css$');

                    foreach ($files as $file) {
                        $this->document->addStyleSheet(JURI::root(true) . '/components/com_jce/editor/tiny_mce/themes/advanced/skins/' . basename($theme) . '/' . $file);
                    }
                }

                // assign references
                $this->assignRef('lists', $lists);
                $this->assignRef('profile', $row);
                $this->assignRef('rows', $rows);
                $this->assignRef('params', $params);
                $this->assignRef('plugins', $plugins);

                $options = WFToolsHelper::getOptions($params);

                $this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Profiles.init(' . json_encode($options) . ')});');

                // set toolbar
                if ($row->id) {
                    JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_PROFILES_EDIT') . ' - [' . $row->name . ']', 'logo.png');
                } else {
                    JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_PROFILES_NEW'), 'logo.png');
                }

                // set buttons
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