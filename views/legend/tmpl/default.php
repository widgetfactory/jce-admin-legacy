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
?>
<form action="index.php?option=com_jce" method="post" name="adminForm">
	<div id="jce">
		<fieldset>
			<table class="ui-widget ui-widget-content">
				<thead>
					<tr class="ui-widget-header">
						<th nowrap="nowrap" width="20%" class="title"><?php echo WFText::_('WF_LEGEND_NAME');?>
						</th>
						<th nowrap="nowrap" class="title"><?php echo WFText::_('WF_LEGEND_BUTTON');?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->plugins as $plugin) :
					if ($plugin->icon) :
						$language = JFactory::getLanguage();
						// load language
						if ($plugin->core == 0) {
							$language->load('com_jce_' . trim($plugin->name), JPATH_SITE);
						}
					
						$icon = $this->model->getIcon($plugin);
					?>
					<tr title="<?php echo $plugin->icon;?>">
						<td width="50%"><p class="title"><?php echo WFText::_($plugin->title);?></p><p class="description"><?php echo WFText::_($plugin->description, '');?></p></td>
						<td width="50%"><span class="defaultSkin" title="<?php echo WFText::_($plugin->title);?>"><?php echo $icon;?></span></td>
					</tr>
				<?php
					endif;
				endforeach;?>
				</tbody>
			</table>
		</fieldset>
	</div>
	<input type="hidden" name="option" value="com_jce" />
	<input type="hidden" name="task" value="" /> <input type="hidden" name="type" value="group" /> <?php echo JHTML::_('form.token');?>
</form>
