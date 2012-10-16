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

defined('JPATH_BASE') or die('RESTRICTED');
jimport('joomla.installer.adapters.language');
/**
 * Language installer
 *
 * @package		JCE
 * @subpackage	Installer
 * @since		1.5
 */
class WFInstallerLanguage extends JInstallerLanguage
{
    public function install() {
        return parent::install();
    }
    
    public function uninstall($id) {
        return parent::uninstall($id, $clientId = null);
    }
}
