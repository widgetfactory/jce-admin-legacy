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

wfimport('admin.helpers.xml');
wfimport('admin.helpers.extension');

class WFControllerEditor extends JController
{
	function __construct($config = array())
	{
	}

	function execute($task)
	{
		// Load language
		$language = JFactory::getLanguage();
		$language->load('com_jce', JPATH_ADMINISTRATOR);

		$layout = JRequest::getCmd('layout');
		$plugin = JRequest::getCmd('plugin');

		if ($layout) {
			switch ($layout) {
				case 'editor':
					if ($task == 'pack') {
						jimport('joomla.application.component.model');

						JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'models');

						require_once(WF_EDITOR_CLASSES . DS . 'editor.php');

						$model = JModel::getInstance('editor', 'WFModel');
						$model->pack();
					}	
					break;
				case 'theme':
					$theme = JRequest::getWord('theme');

					if ($theme && is_dir(WF_EDITOR_THEMES . DS . $theme)) {
						require_once(WF_EDITOR_THEMES . DS . $theme .DS. 'theme.php');
					} else {
						JError::raiseError(500, WFText::_('Theme not found!'));
					}

					break;
				case 'plugin':
					$file = basename(JRequest::getCmd('file', $plugin));
					$path = WF_EDITOR_PLUGINS . DS . $plugin;

					if (is_dir($path) && file_exists($path . DS . $file . '.php')) {
						include_once($path . DS . $file . '.php');
					} else {
						JError::raiseError(500, WFText::_('File ' . $file . ' not found!'));
					}

					break;
			}
			exit();
		} else {
			JError::raiseError(500, WFText::_('No Layout'));
		}

	}
}
?>