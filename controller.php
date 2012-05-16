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

jimport('joomla.application.component.controller');

class WFController extends JController {

    /**
     * Custom Constructor
     */
    public function __construct($default = array()) {
        parent::__construct($default);

        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');

        // load helpers
        wfimport('admin.helpers.parameter');
        wfimport('admin.helpers.extension');
        wfimport('admin.helpers.xml');
    }

    private function loadMenu() {
        $view = JRequest::getWord('view', 'cpanel');
        $model = $this->getModel($view);

        JSubMenuHelper::addEntry(WFText::_('WF_CPANEL'), 'index.php?option=com_jce&view=cpanel', $view == 'cpanel');

        $subMenus = array(
            'WF_CONFIGURATION' => 'config',
            'WF_PROFILES' => 'profiles',
            'WF_INSTALL' => 'installer'
        );

        if (JPluginHelper::isEnabled('system', 'jcemediabox')) {
            $subMenus['WF_MEDIABOX'] = 'mediabox';
        }

        foreach ($subMenus as $menu => $item) {
            if ($model->authorize($item)) {
                JSubMenuHelper::addEntry(WFText::_($menu), 'index.php?option=com_jce&view=' . $item, $view == $item);
            }
        }
    }

    /**
     * Create the View. 
     * This is an overloaded function of JController::getView 
     * and includes addition of the JDocument Object with required scripts and styles
     * @return object
     */
    public function getView($name = '', $type = '', $prefix = '', $config = array()) {
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_ADMINISTRATOR);

        $document = JFactory::getDocument();

        if (!$name) {
            $name = JRequest::getWord('view', 'cpanel');
        }

        if (!$type) {
            $type = $document->getType();
        }

        if (empty($config)) {
            $config = array(
                'base_path' => dirname(__FILE__)
            );
        }

        $view = parent::getView($name, $type, $prefix, $config);

        $document = JFactory::getDocument();
        $document->setTitle(WFText::_('WF_ADMINISTRATION') . ' :: ' . WFText::_('WF_' . strtoupper($name)));

        $model = $this->getModel($name);

        // jquery versions
        $document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/jquery/jquery-' . WF_JQUERY . '.min.js?version=' . $model->getVersion());
        $document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/jquery/jquery-ui-' . WF_JQUERYUI . '.custom.min.js?version=' . $model->getVersion());

        // jQuery noConflict
        $document->addScriptDeclaration('jQuery.noConflict();');

        $scripts = array();

        switch ($name) {
            case 'help':
                $scripts[] = 'help.js';

                break;
            default:
                // load Joomla! core javascript
                if (method_exists('JHtml', 'core')) {
                    JHtml::core();
                }

                require_once(JPATH_ADMINISTRATOR . DS . 'includes' . DS . 'toolbar.php');

                JToolBarHelper::title(WFText::_('WF_ADMINISTRATION') . ' &rsaquo;&rsaquo; ' . WFText::_('WF_' . strtoupper($name)), 'logo.png');

                $params = WFParameterHelper::getComponentParams();
                $theme = $params->get('preferences.theme', 'jce');

                $scripts = array_merge(array(
                    'tips.js',
                    'html5.js'
                        ));

                // Load admin scripts
                $document->addScript(JURI::root(true) . '/administrator/components/com_jce/media/js/jce.js?version=' . $model->getVersion());

                $options = array(
                    'labels' => array(
                        'ok' => WFText::_('WF_LABEL_OK'),
                        'cancel' => WFText::_('WF_LABEL_CANCEL'),
                        'select' => WFText::_('WF_LABEL_SELECT'),
                        'save' => WFText::_('WF_LABEL_SAVE'),
                        'saveclose' => WFText::_('WF_LABEL_SAVECLOSE'),
                        'alert' => WFText::_('WF_LABEL_ALERT'),
                        'required' => WFText::_('WF_MESSAGE_REQUIRED')
                    )
                );

                $document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.init(' . json_encode($options) . ');});');

                $view->addHelperPath(dirname(__FILE__) . DS . 'helpers');
                $this->addModelPath(dirname(__FILE__) . DS . 'models');

                $view->loadHelper('toolbar');
                $view->loadHelper('tools');
                $view->loadHelper('xml');
                $view->loadHelper($name);

                $this->loadMenu();

                break;
        }

        if ($model = $this->getModel($name)) {
            $view->setModel($model, true);
        }

        // Load site scripts
        foreach ($scripts as $script) {
            $document->addScript(JURI::root(true) . '/components/com_jce/editor/libraries/js/' . $script . '?version=' . $model->getVersion());
        }

        require_once(dirname(__FILE__) . DS . 'helpers' . DS . 'system.php');

        $app = JFactory::getApplication();
        $app->registerEvent('onAfterRender', 'WFSystemHelper');

        $view->assignRef('document', $document);

        return $view;
    }

    public function pack() {
        
    }

    /**
     * Display View
     * @return 
     */
    public function display($cachable = false, $params = false) {
        $view = $this->getView();
        $view->display();
    }

    /**
     * Generic cancel method
     * @return 
     */
    public function cancel() {
        // Check for request forgeries
        JRequest::checkToken() or die('Invalid Token');
        $this->setRedirect(JRoute::_('index.php?option=com_jce&view=cpanel', false));
    }

    public function check() {
        // we already no its broken..
        if (JRequest::getCmd('task') == 'repair') {
            return;
        }

        // add models path
        JModel::addIncludePath(dirname(__FILE__) . DS . 'models');
        $profiles = JModel::getInstance('profiles', 'WFModel');

        $state = $profiles->checkTable();

        // Check Profiles DB
        if (!$state) {
            $link = JHTML::link('index.php?option=com_jce&amp;task=repair&amp;type=tables', WFText::_('WF_DB_CREATE_RESTORE'));
            self::_redirect(WFText::_('WF_DB_PROFILES_ERROR') . ' - ' . $link, 'error');
        }

        if ($state) {
            if (!$profiles->checkTableContents()) {
                $link = JHTML::link('index.php?option=com_jce&amp;task=repair&amp;type=tables', WFText::_('WF_DB_CREATE_RESTORE'));
                self::_redirect(WFText::_('WF_DB_PROFILES_ERROR') . ' - ' . $link, 'error');
            }
        }

        jimport('joomla.plugin.helper');

        // Check Editor is installed
        if (JPluginHelper::getPlugin('editors', 'jce') === false) {
            $link = JHTML::link('index.php?option=com_jce&amp;task=repair&amp;type=editor', WFText::_('WF_EDITOR_INSTALL'));
            self::_redirect(WFText::_('WF_EDITOR_INSTALLED_MANUAL_ERROR') . ' - ' . $link, 'error');
        }
    }

    public function repair() {
        $app = JFactory::getApplication();
        $type = JRequest::getWord('type', 'tables');

        switch ($type) {
            case 'tables' :
                // add models path
                JModel::addIncludePath(dirname(__FILE__) . DS . 'models');
                $profiles = JModel::getInstance('profiles', 'WFModel');

                $profiles->installProfiles();
                
                $this->setRedirect(JRoute::_('index.php?option=com_jce&view=cpanel', false));

                break;
            case 'editor' :
                $source = dirname(__FILE__) . DS . 'packages' . DS . 'editors';

                if (is_dir($source)) {
                    jimport('joomla.installer.installer');

                    $installer = new JInstaller();
                    if ($installer->install($source)) {
                        $app->enqueueMessage(WFText::_('WF_EDITOR_INSTALL_SUCCESS'));
                    } else {
                        $app->enqueueMessage(WFText::_('WF_EDITOR_INSTALL_FAILED'));
                    }

                    $this->setRedirect(JRoute::_('index.php?option=com_jce&view=cpanel', false));
                }

                break;
        }
    }

    public function authorize($task) {
        $view = JRequest::getWord('view', 'cpanel');

        $model = $this->getModel($view);

        if (!$model->authorize($task)) {

            if ($model->authorize('manage')) {
                $this->setRedirect('index.php?option=com_jce', WFText::_('JERROR_ALERTNOAUTHOR'), 'error');
            } else {
                $this->setRedirect('index.php', WFText::_('JERROR_ALERTNOAUTHOR'), 'error');
            }
            return false;
        }

        return true;
    }
    
    private static function _redirect($msg = '', $state = '') {
        $app = JFactory::getApplication();

        if ($msg) {
            $app->enqueueMessage($msg, $state);
        }
        JRequest::setVar('view', 'cpanel');
        JRequest::setVar('task', '');

        return false;
    }

}

?>