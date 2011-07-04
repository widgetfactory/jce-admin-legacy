<?php 
/**
 * @version		$Id: form_editor.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
<div id="editor_params">
<?php
	foreach ($this->params->getGroups() as $group => $num) : ?>
		<div id="tabs-editor-<?php echo $group?>">
			<h2><?php echo WFText::_('WF_PROFILES_EDITOR_' . strtoupper($group)); ?></h2>
			<?php echo $this->params->render('params[editor]', $group);?>
		</div>
	<?php endforeach;?>
</div>