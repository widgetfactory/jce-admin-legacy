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
<div>
	<dl class="adminformlist">
		<dt><?php echo WFText::_('WF_INSTALLER_UNINSTALL_DESC');?></dt>
		<dd>
			<label for="install_uninstall" class="tooltip" title="<?php echo WFText::_('WF_INSTALLER_UNINSTAL'); ?>::<?php echo WFText::_('WF_INSTALLER_UNINSTALL_DESC'); ?>"><?php echo WFText::_('WF_INSTALLER_UNINSTAL'); ?></label>
			<span>
				<button class="install_uninstall"><?php echo WFText::_('WF_INSTALLER_UNINSTALL_SELECTED');?></button>
			</span>
		</dd>
	</dl>
</div>
<div id="tabs">
	<ul>
		<li class="tooltip" title="<?php echo JText :: _('WF_INSTALLER_PLUGINS') . '::' . WFText::_('WF_INSTALLER_PLUGINS_DESC');?>"><a href="#tabs-plugins"><?php echo JText :: _('WF_INSTALLER_PLUGINS');?></a></li>
		<li class="tooltip" title="<?php echo JText :: _('WF_INSTALLER_EXTENSIONS') . '::' . WFText::_('WF_INSTALLER_EXTENSIONS_DESC');?>"><a href="#tabs-extensions"><?php echo JText :: _('WF_INSTALLER_EXTENSIONS');?></a></li>
		<li class="tooltip" title="<?php echo JText :: _('WF_INSTALLER_LANGUAGES') . '::' . WFText::_('WF_INSTALLER_LANGUAGES_DESC');?>"><a href="#tabs-languages"><?php echo JText :: _('WF_INSTALLER_LANGUAGES');?></a></li>
		<li class="tooltip" title="<?php echo JText :: _('WF_INSTALLER_RELATED') . '::' . WFText::_('WF_INSTALLER_RELATED_DESC');?>"><a href="#tabs-related"><?php echo JText :: _('WF_INSTALLER_RELATED');?></a></li>
	</ul>
	<div id="tabs-plugins">
		<?php if (count($this->plugins)) : ?>
			<?php echo $this->loadTemplate('plugins');?>
		<?php else : ?>
			<?php echo WFText::_('WF_INSTALLER_NO_PLUGINS'); ?>
		<?php endif; ?>
	</div>
	<div id="tabs-extensions">
    <?php if (count($this->extensions)) : ?>
      <?php echo $this->loadTemplate('extensions');?>
    <?php else : ?>
      <?php echo WFText::_('WF_INSTALLER_EXTENSIONS'); ?>
    <?php endif; ?>
  </div>
	<div id="tabs-languages">
		<?php if (count($this->languages)) : ?>
			<?php echo $this->loadTemplate('languages');?>
		<?php else : ?>
			<?php echo WFText::_('WF_INSTALLER_NO_LANGUAGES'); ?>
		<?php endif; ?>
	</div>
	<div id="tabs-related">
		<?php if (count($this->related)) : ?>
			<?php echo $this->loadTemplate('related');?>
		<?php else : ?>
			<?php echo WFText::_('WF_INSTALLER_NO_RELATED'); ?>
		<?php endif; ?>
	</div>
</div>