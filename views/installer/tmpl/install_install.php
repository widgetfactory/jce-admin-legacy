<?php 
/**
 * @version		$Id: install_install.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
<dl class="adminformlist">	
	<dt><?php echo WFText::_('WF_INSTALLER_INSTALL_DESC');?></dt>
	<dd>
		<label for="install" class="hasTip" title="<?php echo WFText::_('WF_INSTALLER_PACKAGE'); ?>::<?php echo WFText::_('WF_INSTALLER_PACKAGE_DESC'); ?>"><?php echo WFText::_('WF_INSTALLER_PACKAGE'); ?>:</label>
		<span>
			<input type="file" name="install" id="upload" placeholder="<?php echo $this->state->get('install.directory'); ?>" />
			<button id="install_button"><?php echo WFText::_('WF_INSTALLER_UPLOAD'); ?></button>
		</span>
	</dd>
</dl>