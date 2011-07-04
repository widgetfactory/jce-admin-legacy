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

defined('_JEXEC') or die('ERROR_403'); 
?>
<form action="index.php?option=com_jce&tmpl=component" method="post" name="adminForm">
	<div id="jce">
	    <table width="100%" cellspacing="0" id="users-toolbar">
			<tr>
				<td width="100%">
					<?php echo WFText::_('WF_LABEL_FILTER'); ?>:
					<input type="text" name="search" id="search" size="30" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button id="filter_go" onclick="this.form.submit();"><?php echo WFText::_('WF_LABEL_GO'); ?></button>
					<button id="filter_reset" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo WFText::_('WF_LABEL_RESET'); ?></button>
				</td>
				<td nowrap="nowrap">
					<?php echo $this->lists['group'];?>
				</td>
			</tr>
		</table>
		<br />
		<table cellspacing="1" id="users-list">
			<thead>
				<tr>
					<th width="20px" class="title" align="center">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   'WF_USERS_NAME', 'a.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
					</th>
					<th width="20%" class="title" >
						<?php echo JHTML::_('grid.sort',   'WF_USERS_USERNAME', 'a.username', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
					</th>
					<th width="20%" class="title">
						<?php echo JHTML::_('grid.sort',   'WF_USERS_GROUP', 'groupname', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
				$k = 0;
				for ($i=0, $n=count( $this->items ); $i < $n; $i++)
				{
					$row 		= $this->items[$i];	
				?>
				<tr>
					<td>
						<?php echo JHTML::_('grid.id', $i, $row->id );?>
					</td>
					<td>
						<?php echo $row->name; ?>
	                </td>
					<td>
						<span id="username_<?php echo $row->id;?>"><?php echo $row->username; ?></span>
					</td>
					<td>
						<?php echo WFText::_( $row->groupname ); ?>
					</td>
				</tr>
				<?php
					$k = 1 - $k;
					}
				?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="option" value="com_jce" />
	<input type="hidden" name="task" value="addusers" />
    <input type="hidden" name="view" value="users" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>