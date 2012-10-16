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

jimport('joomla.installer.adapters.plugin');

/**
 * JCE Plugin installer
 *
 * @package   JCE
 * @subpackage  Installer
 * @since   1.5
 */
class WFInstallerPlugin extends JInstallPlugin {

    /**
     * Install method
     *
     * @access  public
     * @return  boolean True on success
     */
    public function install() {
        $ret = parent::install();

        // post-install
        if (!defined('JPATH_PLATFORM')) {
            $scriptfile = $this->manifest->getElementByPath('scriptfile');
            $scriptfile = $this->parent->getPath('source') . '/' . $scriptfile;

            if (is_file($scriptfile)) {
                include_once($scriptfile);
            }

            // Set the class name
            $classname = $this->get('name') . 'InstallerScript';

            if (class_exists($classname)) {
                // Create a new instance
                $manifestClass = new $classname($this);
            }
            
            $msg = '';

            // And now we run the postflight
            ob_start();
            ob_implicit_flush(false);

            if ($manifestClass && method_exists($manifestClass, 'postflight')) {
                $manifestClass->postflight('install', $this);
            }

            // Append messages
            $msg .= ob_get_contents();
            ob_end_clean();

            $this->parent->set('extension.message', $msg);
        }

        return $ret;
    }

    /**
     * Uninstall method
     *
     * @access  public
     * @param 	string   $name  The name of the plugin to uninstall
     * @return  boolean True on success
     */
    public function uninstall($id) {
        $ret = parent::uninstall($id, $clientId = 0);

        // post-install
        if (!defined('JPATH_PLATFORM')) {
            $scriptfile = $this->manifest->getElementByPath('scriptfile');
            $scriptfile = $this->parent->getPath('source') . '/' . $scriptfile;

            if (is_file($scriptfile)) {
                include_once($scriptfile);
            }

            // Set the class name
            $classname = 'plg' . str_replace('-', '', $row->folder) . $row->element . 'InstallerScript';

            if (class_exists($classname)) {
                // Create a new instance
                $manifestClass = new $classname($this);
            }
            
            $msg = '';

            // And now we run the postflight
            ob_start();
            ob_implicit_flush(false);

            if ($manifestClass && method_exists($manifestClass, 'uninstall')) {
                $manifestClass->uninstall($this);
            }

            // Append messages
            $msg .= ob_get_contents();
            ob_end_clean();

            $this->parent->set('extension.message', $msg);
        }
    }

}