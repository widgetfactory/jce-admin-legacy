<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2013 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

class pkg_jceInstallerScript {
    
    public function preflight($type, $parent) {
        $installer = $parent->parent;
        
        require_once($installer->getPath('source') . '/administrator/components/com_jce/install.php');
        
        $requirements = WFInstall::checkRequirements();
        
        if ($requirements !== true) {            
            echo $requirements;
            return false;
        }
        
        return true;
    }

    public function install($parent) {}

    public function uninstall() {}

    public function update($parent) {}

    public function postflight($type, $parent, $results) {
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/install.php');

        // enable module
        $plugin = JTable::getInstance('extension');
        $plugin->find(array('type' => 'plugin', 'element' => 'jcefilebrowser'))->publish();
        
        jimport('joomla.filesystem.file');
        
        foreach($results as $extension) {

            $path = '';
            
            switch($extension['name']) {
                case 'plg_editors_jce':
                    $path = array(JPATH_PLUGINS . '/editors/jce');
                    break;
                case 'plg_quickicon_jcefilebrowser':
                    $path = array(JPATH_PLUGINS . '/quickicon/jcefilebrowser');
                    break;
            }
            
            if ($path && $extension['result'] === true) {
                WFInstall::addIndexfiles($path);
            }
        }

        self::displayResults($results);
    }

    private static function displayResults($results) {
        $message = '<div id="jce"><style type="text/css" scoped="scoped">' . file_get_contents(dirname(__FILE__) . '/media/css/install.css') . '</style>';

        $message .= '<h2>' . JText::_('WF_ADMIN_TITLE') . '</h2>';
        $message .= '<ul class="install">';
        
        foreach($results as $result) {
            $class  = $result['result'] ? 'success' : 'error';
            $name   = $result['name'] == 'com_jce' ? 'WF_ADMIN_DESC' : $result['name'];
            
            $message .= '<li class="' . $class . '">' . JText::_($name) . '<li>';
        }
        $message .= '</ul>';
        $message .= '</div>';
        
        echo $message;
    }

}

?>
