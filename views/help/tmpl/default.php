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

defined('_JEXEC') or die('RESTRICTED');
?>
<div id="jce">
	<div class="ui-layout-center">
		<div id="help-frame"><iframe id="help-iframe" src="javascript:;" scrolling="auto" frameborder="0"></iframe></div>
	</div>
	<!--div class="ui-layout-north"></div>
	<div class="ui-layout-south"></div>
	<div class="ui-layout-east"></div-->
	<div class="ui-layout-west">
		<div id="help-menu"><?php echo $this->model->renderTopics();?></div>
	</div>
</div>