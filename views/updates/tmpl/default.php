<?php 
/**
 * @version		$Id: default.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
?>
<div id="jce">
	<fieldset>
		<legend><?php echo WFText::_('WF_UPDATES_AVAILABLE');?></legend>
		<table class="ui-widget ui-widget-content" id="updates-list" cellspacing="1">
			<thead>
				<tr class="ui-widget-header">
					<th width="3%"></th>
					<th class="title">
						<?php echo WFText::_('WF_UPDATES_NAME') ?>
					</th>
					<th class="title" width="20%">
						<?php echo WFText::_('WF_UPDATES_TYPE') ?>
					</th>
			        <th class="title" width="20%">
						<?php echo WFText::_('WF_UPDATES_VERSION') ?>
					</th>
					<th class="title" width="20%">
						<?php echo WFText::_('WF_UPDATES_PRIORITY') ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr><td colspan="5"></td></tr>
			</tbody>
		</table>
		</fieldset>
	<fieldset>
		<legend><?php echo WFText::_('WF_UPDATES_INFO') ?></legend>
		<div id="updates-info"></div>
	</fieldset>
	<div style="float:right;margin:10px 0 0 0;"><button id="update-button" class="check"><?php echo WFText::_('WF_UPDATES_CHECK');?></button></div>
</div>