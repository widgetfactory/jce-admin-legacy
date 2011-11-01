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
        
        $theme  	= $params->get('preferences.theme', 'jce');
        $site_path  = JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'libraries' . DS . 'css';
		$admin_path = JPATH_COMPONENT_ADMINISTRATOR . DS . 'media' . DS . 'css';
        
        // Load styles
        $styles = array();
        
        if (!JFolder::exists($site_path  . DS . 'jquery' . DS . $theme)) {
            $theme = 'jce';
        }
        
        if (JFolder::exists($site_path . DS . 'jquery' .DS. $theme)) {
            $files = JFolder::files($site_path . DS . 'jquery' .DS. $theme, '\.css');
            
            foreach ($files as $file) {
                $styles[] = 'components/com_jce/editor/libraries/css/jquery/' . $theme . '/' . $file;
            }
        }

		// admin global css
        $styles = array_merge($styles, array(
            'administrator/components/com_jce/media/css/global.css'
        ));
        
        if (JFile::exists($admin_path . DS . $view . '.css')) {
            $styles[] = 'administrator/components/com_jce/media/css/' . $view . '.css';
        }
        
        return $styles;
    }
    
    function loadStyles()
    {			
        $styles = $this->getStyles();
		
        $out    = '';

		foreach ($styles as $style) {
            $out .= '<link rel="stylesheet" type="text/css" href="' . JURI::root(true) . '/' . $style . '" />' . "\n";
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