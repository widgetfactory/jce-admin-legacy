<?php 
/**
 * @version		$Id: install.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

// load base classes
require_once(dirname(__FILE__) . DS . 'includes' . DS . 'base.php');
// load installer class
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'installer.php');

/**
 * Installer function
 * @return
 */
function com_install()
{
    return com_jceInstallerScript::install();
}
/**
 * Uninstall function
 * @return
 */
function com_uninstall()
{
    return com_jceInstallerScript::uninstall();
}

class com_jceInstallerScript {
	
	function install() 
	{	
	    $installer = WFInstaller::getInstance();
		$installer->install();
	}
	
	function uninstall() 
	{		
	    $installer = WFInstaller::getInstance();
	    $installer->uninstall();
	}
	
	function update() 
	{
		$this->install();
	}
}

?>