<?php
/**
 * @version		$Id: view.html.php 231 2011-06-14 15:47:00Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class WFViewUpdates extends JView
{
    function display($tpl = null)
    {
        $model =$this->getModel();
		
		$this->document->addScript('components/com_jce/media/js/update.js?version=' . $model->getVersion());
		
		$options = array(
			'language' => array(
				'check' 		=> WFText::_('WF_UPDATES_CHECK'),
				'install' 		=> WFText::_('WF_UPDATES_INSTALL'),
				'installed' 	=> WFText::_('WF_UPDATES_INSTALLED'),
				'no_updates'	=> WFText::_('WF_UPDATES_NONE'),
				'high'			=> WFText::_('WF_UPDATES_HIGH'),
				'medium'		=> WFText::_('WF_UPDATES_MEDIUM'),
				'low'			=> WFText::_('WF_UPDATES_LOW'),
				'full'			=> WFText::_('WF_UPDATES_FULL'),
				'patch'			=> WFText::_('WF_UPDATES_PATCH'),
				'auth_failed'	=> WFText::_('WF_UPDATES_AUTH_FAIL'),
				'update_info'	=> WFText::_('WF_UPDATES_INFO'),
				'install_info'	=> WFText::_('WF_UPDATES_INSTALL_INFO'),
				'check_updates'	=> WFText::_('WF_UPDATES_CHECKING')
			)
		);
		
		$options  = json_encode($options);
		
		$this->document->addScriptDeclaration('jQuery(document).ready(function($){$.jce.Update.init('.$options.');});');
        
        parent::display($tpl);
    }
}
?>
