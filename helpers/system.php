<?php
/**
 * @version $Id: system.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class WFSystemHelper extends JPlugin
{
    function getStyles()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        
        wfimport('admin.helpers.extension');
        
        $view = JRequest::getCmd('view', 'cpanel');

        $component 	= WFExtensionHelper::getComponent();        
        $params 	= new WFParameter($component->params);
        
        $theme  = $params->get('preferences.theme', 'jce');
        $path   = JPATH_COMPONENT_ADMINISTRATOR . DS . 'media' . DS . 'css';
        
        // Load styles
        $styles = array();
        
        if (!JFolder::exists($path  . DS . 'jquery' . DS . $theme)) {
            $theme = 'jce';
        }
        
        if (JFolder::exists($path . DS . 'jquery' .DS. $theme)) {
            $files = JFolder::files($path . DS . 'jquery' .DS. $theme, '\.css');
            
            foreach ($files as $file) {
                $styles[] = 'jquery/' . $theme . '/' . $file;
            }
        }
        
        $styles = array_merge($styles, array(
            'global.css'
        ));
        
        jimport('joomla.environment.browser');
        
        $browser = JBrowser::getInstance();
        if ($browser->getBrowser() == 'msie') {
            $styles[] = 'styles_ie.css';
        }
        
        if (JFile::exists($path . DS . $view . '.css')) {
            $styles[] = $view . '.css';
        }
        
        return $styles;
    }
    
    function loadStyles()
    {
        $styles = $this->getStyles();
        $out    = '';
        
        foreach ($styles as $style) {
            $out .= '<link rel="stylesheet" type="text/css" href="' . JURI::root(true) . '/administrator/components/com_jce/media/css/' . $style . '" />' . "\n";
        }
        
        return $out;
    }
    
    function onAfterRender()
    {
        $buffer = JResponse::getBody();
        $buffer = preg_replace('#<head>([\s\S]+?)<\/head>#', '<head>$1' . $this->loadStyles() . '</head>', $buffer);
        
        JResponse::setBody($buffer);
        
        return true;
    }
}
?>