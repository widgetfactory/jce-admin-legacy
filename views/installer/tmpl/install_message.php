<?php 
/**
 * @version		$Id: install_message.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('ERROR_403');
?>
<fieldset>
	<legend><?php echo WFText::_('WF_INSTALLER_SUMMARY_'.strtoupper($this->state->get('action')));?></legend>
	<table class="ui-widget ui-widget-content install-summary">
		<thead class="ui-widget-header">
			<tr>
				<th style="width:75%; text-align:left;" class="title"><?php echo WFText::_('WF_INSTALLER_ADDON');?></th>
				<th style="width:10%; text-align:center;" class="title"><?php echo WFText::_('WF_INSTALLER_TYPE');?></th>
				<th style="width:10%; text-align:center;" class="title" style="text-align:center"><?php echo WFText::_('WF_INSTALLER_VERSION');?></th>
				<th style="width:5%; text-align:center;" class="title"><?php echo WFText::_('WF_INSTALLER_RESULT');?></th>
			</tr>
		</thead>
		<?php foreach ($this->state->get('result') as $item) :
			$class 	= $item['result'] ? 'ok' : 'error';
			$result = $item['result'] ? WFText::_('WF_INSTALLER_SUCCESS') : WFText::_('WF_INSTALLER_ERROR');
		?>
			<tr>
				<td style="font-weight:bold"><?php echo WFText::_($item['name']) ?></td>
				<td style="text-align:center;font-weight:bold"><?php echo WFText::_('WF_INSTALLER_'.$item['type']) ?></td>
				<td style="text-align:center;font-weight:bold"><?php echo WFText::_($item['version']) ?></td>
				<td class="title" style="text-align:center;"><span class="<?php echo $class;?>"></span></td>
			</tr>
			<?php if (isset($item['message'])) : ?>
				<tr>
					<td colspan="4"><?php echo WFText::_($item['message'], $item['message']) ?></td>
				</tr>
			<?php endif;?>
			<?php if (isset($item['extension.message'])) : ?>
			<tr>
				<td colspan="4"><?php echo WFText::_($item['extension.message'], $item['extension.message']) ?></td>
			</tr>
			<?php endif;?>
	<?php endforeach;?>
	</table>
</fieldset>