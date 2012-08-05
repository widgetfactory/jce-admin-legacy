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

defined('_JEXEC') or die('RESTRICTED');

?>
<dl class="adminformlist">	
	<dt><?php echo WFText::_('WF_INSTALLER_INSTALL_DESC');?></dt>
	<dd>
		<label for="install" class="tooltip" title="<?php echo WFText::_('WF_INSTALLER_PACKAGE'); ?>::<?php echo WFText::_('WF_INSTALLER_PACKAGE_DESC'); ?>"><?php echo WFText::_('WF_INSTALLER_PACKAGE'); ?></label>
		<span>
			<input type="file" name="install" id="upload" placeholder="<?php echo $this->state->get('install.directory'); ?>" />
			<button id="install_button"><?php echo WFText::_('WF_INSTALLER_UPLOAD'); ?></button>
		</span>
	</dd>
</dl>