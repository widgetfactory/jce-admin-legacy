<?php
/**
 * @package   	JCE
 * @copyright 	Copyright � 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

// load base classes
require_once(dirname(__FILE__) . DS . 'includes' . DS . 'base.php');
// get the view
$view = JRequest::getCmd('view', 'cpanel');
// get task
$task = JRequest::getCmd('task');

// legacy conversion
if ($task == 'popup') {
	$view = 'popup';
	JRequest::setVar('task', null);
}

// import library dependencies
jimport('joomla.application.component.helper');
jimport('joomla.application.component.controller');

// Require the base controller
require_once (dirname( __FILE__ ) . DS . 'controller.php');

// Load controller
$controllerPath = dirname(__FILE__) . DS . 'controller' . DS . $view . '.php';

if (file_exists($controllerPath)) {
    require_once ($controllerPath);

    $controllerClass = 'WFController'.ucfirst($view);
    $controller = new $controllerClass(array(
    	'base_path' => dirname(__FILE__)
    ));
// load default controller
} else {
    $controller = new WFController(array(
    	'base_path' => dirname(__FILE__)
    ));
}

// check Authorisations
switch ($view) {
	case 'editor':
	case 'help':
	case 'popup':	
		break;
	default:
		if ($view == 'cpanel') {
			$view = 'manage';
		}
		// Authorise
		$controller->authorize($view);
		
		$installer = WFInstaller::getInstance();
		$installer->check();
	
		break;	
}

// Perform the Request task
$controller->execute($task);
$controller->redirect();
?>
