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

wfimport('admin.classes.view');

class WFViewCpanel extends WFView {

    function display($tpl = null) {
        wfimport('admin.models.updates');

        $mainframe = JFactory::getApplication();

        $model = $this->getModel();
        $version = $model->getVersion();

        $component = WFExtensionHelper::getComponent();

        // get params definitions
        $params = new WFParameter($component->params, '', 'preferences');

        $canUpdate = WFModelUpdates::canUpdate() && WFModel::authorize('installer');

        $options = array(
            'feed' => (int) $params->get('feed', 0),
            'updates' => (int) $params->get('updates', $canUpdate ? 1 : 0),
            'labels' => array(
                'feed' => WFText::_('WF_CPANEL_FEED_LOAD'),
                'updates' => WFText::_('WF_UPDATES'),
                'updates_available' => WFText::_('WF_UPDATES_AVAILABLE')
            )
        );

        JHtml::_('behavior.modal');

        $this->addScript('components/com_jce/media/js/cpanel.js?version=' . $model->getVersion());

        $this->addScriptDeclaration('jQuery(document).ready(function($){$.jce.CPanel.init(' . json_encode($options) . ')});');

        // load styles
        $this->addStyleSheet(JURI::root(true) . '/administrator/components/com_jce/media/css/cpanel.css');

        if (WFModel::authorize('preferences')) {
            WFToolbarHelper::preferences();
        }

        if (WFModel::authorize('installer')) {
            WFToolbarHelper::updates($canUpdate);
        }

        WFToolbarHelper::help('cpanel.about');

        $views = array('config', 'profiles', 'installer', 'browser', 'mediabox');

        $icons = array();

        foreach ($views as $view) {
            // check if its allowed...
            if (WFModel::authorize($view) === false) {
                continue;
            }
            
            $attribs        = array('target="_self"');
            $title          = 'WF_' . strtoupper($view);
            $description    = 'WF_' . strtoupper($view) . '_DESC';
            $link           = 'index.php?option=com_jce&amp;view=' . $view;

            if ($view == 'browser') {
                $link = WFModel::getBrowserLink();
                
                if (empty($link)) {
                    continue;
                }
                
                $options        = str_replace('"', "'", json_encode(array('width' => 780, 'height' => 560)));
                $rel            = str_replace('"', "'", json_encode(array('handler' => 'iframe', 'size' => array('x' => 780, 'y' => 560))));
                
                $attribs        = array('target="_blank"', 'class="browser modal"', 'rel="' . $rel . '"', 'data-options="' . $options . '"');
                
                $title          = 'WF_' . strtoupper($view) . '_TITLE';
                $description    = 'WF_CPANEL_' . strtoupper($view);
            }

            // if its mediabox, check the plugin is installed and enabled
            if ($view == 'mediabox' && !JPluginHelper::isEnabled('system', 'jcemediabox')) {
                continue;
            }

            $icons[] = '<li class="cpanel-icon wf-tooltip" title="' . WFText::_($title) . '::' . WFText::_($description) . '"><a href="' . $link . '"' . implode(' ', $attribs) . '><span class="' . $view . '"></span>' . WFText::_($title) . '</a></li>';
        }

        $this->assign('icons', $icons);
        $this->assignRef('model', $model);
        $this->assignRef('installer', $installer);
        $this->assignRef('params', $params);

        $this->assignRef('version', $version);

        parent::display($tpl);
    }

}

?>
