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
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="jce">
		<table id="profiles-toolbar" cellspacing="0">
			<tr>
				<td>
					<label for="search"><?php echo WFText::_('WF_LABEL_FILTER'); ?></label><input type="text" name="search" id="search" size="50" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<button id="filter_go" onclick="this.form.submit();"><?php echo WFText::_('WF_LABEL_GO'); ?></button>
					<button id="filter_reset" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo WFText::_('WF_LABEL_RESET'); ?></button>
				</td>

				<td nowrap="nowrap">
					<span class="upload-container">
						<label for="import"><?php echo WFText::_('WF_PROFILES_IMPORT'); ?></label>
						<input type="file" name="import" id="upload" accept="application/xml" />
						<button id="upload_button"><?php echo WFText::_('WF_PROFILES_IMPORT_IMPORT'); ?></button>								
					</span>
				</td>
			</tr>			
		</table>
		<br />
		<table id="profiles-list" cellspacing="1">
		<thead>
			<tr>
				<th width="3%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows );?>);" />
				</th>
				<th class="title" width="20%">
					<?php echo JHTML::_('grid.sort',   'WF_PROFILES_NAME', 'p.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
		        <th class="title" width="60%">
					<?php echo JHTML::_('grid.sort',   'WF_PROFILES_DESCRIPTION', 'p.description', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHTML::_('grid.sort',   'WF_PROFILES_STATE', 'p.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
		        <th nowrap="nowrap" width="10%" >
					<?php echo JHTML::_('grid.sort',   'WF_PROFILES_ORDERING', 'p.ordering', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
					<?php if( count( $this->rows ) > 1 ){ echo JHTML::_('grid.order',  $this->rows );}?>
				</th>
				<th nowrap="nowrap"  width="1%" class="title">
					<?php echo JHTML::_('grid.sort',   'WF_LABEL_ID', 'p.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$rows = $this->rows;
			$k = 0;
			for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = $rows[$i];
		
			$link 		= JRoute::_( 'index.php?option=com_jce&view=profiles&task=edit&cid[]='. $row->id );
			
			// state
			$state 		= JHTML::_('grid.published', $row, $i );
			
			// checked out
			$checked 	= JHTML::_('grid.checkedout', $row, $i );
		?>
			<tr>
				<td align="center">
					<?php echo $checked; ?>
				</td>
				<td>
					<?php
					if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
						echo $row->name;
					} else {	
					?>
						<span class="editlinktip tooltip" title="<?php echo WFText::_( 'WF_PROFILES_EDIT' );?>::<?php echo $row->name; ?>">
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?></a></span>
					<?php } ?>
				</td>
		        <td>
					<?php echo $row->description;?>
				</td>
				<td align="center">
					<?php echo $state;?>
				</td>
		        <td class="order" align="center">
					<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'WF_PROFILES_ORDER_UP', $row->ordering ); ?></span>
					<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'WF_PROFILES_ORDER_DOWN', $row->ordering ); ?></span>
					<?php $disabled = $n > 1 ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
				</td>
				<td align="center">
					<?php echo $row->id;?>
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
    <input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>