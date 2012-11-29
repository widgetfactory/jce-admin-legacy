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
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/install.php');
        
        $installer = method_exists($parent, 'getParent') ? $parent->getParent() : $parent->parent;

        return WFInstall::install($installer);
    }

    public function uninstall() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/install.php');
        
        return WFInstall::uninstall();
    }

    public function update($parent) {
        return $this->install($parent);
    }
}

/**
 * Installer function
 * @return
 */
function com_install() {

    if (!defined('JPATH_PLATFORM')) {
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/install.php');
        
        $installer = JInstaller::getInstance();
        return WFInstall::install($installer);
    }

    return true;
}

/**
 * Uninstall function
 * @return
 */
function com_uninstall() {

    if (!defined('JPATH_PLATFORM')) {
        require_once(JPATH_ADMINISTRATOR . '/components/com_jce/install.php');
        
        return WFInstall::uninstall();
    }

    return true;
}
?>
