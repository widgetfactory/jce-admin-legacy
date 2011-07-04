<?php 
/**
 * @version		$Id: install_related.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
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
<table cellspacing="1">
	<thead>
		<tr>
			<th width="20px" align="center">&nbsp;</th>
			<th><?php echo WFText::_( 'WF_LABEL_NAME' ); ?></th>
			<th width="10%" align="center"><?php echo WFText::_( 'WF_LABEL_VERSION' ); ?></th>
			<th width="15%" align="center"><?php echo WFText::_( 'WF_LABEL_DATE' ); ?></th>
			<th width="25%" align="center"><?php echo WFText::_( 'WF_LABEL_AUTHOR' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
	foreach ($this->related as $related) : ?>
		<tr>
			<td width="20px" align="center"><input type="checkbox" name="rid[]" value="<?php echo $related->id;?>" /></td>
			<td><span class="bold"><?php echo WFText::_($related->name); ?></span></td>
			<td align="center"><?php echo @$related->version != '' ? $related->version : '&nbsp;'; ?></td>
			<td align="center"><?php echo @$related->creationdate != '' ? $related->creationdate : '&nbsp;'; ?></td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo WFText::_( 'WF_LABEL_AUTHOR_INFO' );?>::<?php echo $related->authorUrl; ?>">
					<?php echo @$related->author != '' ? $related->author : '&nbsp;'; ?>
				</span>
			</td>
		</tr>
	<?php 
	endforeach; ?>
	</tbody>
</table>