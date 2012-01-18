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

// define Joomla! version
define('WF_JOOMLA15', version_compare(JVERSION, '1.6', '<'));

// define JQuery version
define('WF_JQUERY', '1.7.1');
// define JQuery UI version
define('WF_JQUERYUI', '1.8.17');

// Some shortcuts to make life easier

// JCE Administration Component
define('WF_ADMINISTRATOR',     JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce');
// JCE Site Component
define('WF_SITE',              JPATH_SITE . DS . 'components' . DS . 'com_jce');
// JCE Plugin
if (WF_JOOMLA15) {
    define('WF_PLUGIN',        JPATH_SITE . DS . 'plugins' . DS . 'editors');
} else {
    define('WF_PLUGIN',        JPATH_SITE . DS . 'plugins' . DS . 'editors' . DS . 'jce');
}
// JCE Editor
define('WF_EDITOR',            WF_SITE . DS . 'editor');
// JCE Editor Plugins
define('WF_EDITOR_PLUGINS',    WF_EDITOR . DS . 'tiny_mce' . DS . 'plugins');
// JCE Editor Themes
define('WF_EDITOR_THEMES',     WF_EDITOR . DS . 'tiny_mce' . DS . 'themes');
// JCE Editor Libraries
define('WF_EDITOR_LIBRARIES',  WF_EDITOR . DS . 'libraries');
// JCE Editor Classes
define('WF_EDITOR_CLASSES',    WF_EDITOR_LIBRARIES . DS . 'classes');
// JCE Editor Extensions
define('WF_EDITOR_EXTENSIONS', WF_EDITOR . DS . 'extensions');

?>