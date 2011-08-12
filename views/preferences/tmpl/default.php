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
<form action="index.php" method="post" name="adminForm">
	<div id="jce">
		<div id="tabs">
		    <ul>
		    <?php foreach ($this->params->getGroups() as $group => $num) : ?>
		    	<li><a href="#tabs-<?php echo $group;?>"><?php echo JText :: _('WF_PREFERENCES_' . strtoupper($group));?></a></li>
		    <?php endforeach;?>
		    <?php if ($this->permissons) : ?>
		    	<li><a href="#tabs-access"><?php echo JText :: _('WF_PREFERENCES_PERMISSIONS');?></a></li>
		    <?php endif;?>		
		    </ul>	
		    <?php foreach ($this->params->getGroups() as $group => $num) : ?>
				<div id="tabs-<?php echo $group?>">
					<?php echo $this->params->render('params[preferences]', $group);?>
				</div>
			<?php endforeach;?>
			<?php if ($this->permissons) : ?>
				<div id="tabs-access">
					<?php
					
					if (!class_exists('JForm')) : 
						echo '<div id="access-accordian">';
					endif;
					
					foreach ($this->permissons as $field):
					?>
						<?php echo $field->input; ?>
					<?php
					endforeach;
					
					if (!class_exists('JForm')) : 
						echo '</div>';
					endif;
					?>
				</div>
			<?php endif;?>
		</div>
	</div>
    <input type="hidden" name="option" value="com_jce" />
    <input type="hidden" name="view" value="preferences" />
    <input type="hidden" name="task" value="" />
    <?php echo JHTML::_('form.token'); ?>
</form>