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

class com_jceInstallerScript {

    public function install($parent) {
        if (!class_exists('WFInstall')) {
            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce' . DS . 'install.php');
        }

        $installer = method_exists($parent, 'getParent') ? $parent->getParent() : $parent->parent;

        return WFInstall::install($installer);
    }

    public function uninstall() {
        if (!class_exists('WFInstall')) {
            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce' . DS . 'install.php');
        }

        return WFInstall::uninstall();
    }

    public function update($parent) {
        return $this->install($parent);
    }

    function preflight($type, $parent) {
        $db = JFactory::getDBO();
        
        // remove admin menu emtries
        $db = JFactory::getDBO();
        $db->setQuery('DELETE FROM #__menu WHERE alias = "jce" AND menutype = "main"');
        
        $db = JFactory::getDBO();
        $db->setQuery('DELETE FROM #__menu WHERE alias LIKE "wf-menu-%" AND menutype = "main"');
        
        $db->query();
        $db->setQuery('DELETE FROM #__assets WHERE title = "com_jce"');
        $db->query();
    }
}

?>