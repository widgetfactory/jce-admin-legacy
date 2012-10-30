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

$message1 = $this->state->get('message');
$message2 = $this->state->get('extension_message');
?>
<table class="adminform">
    <tbody>
        <?php if ($message1) : ?>
            <tr>
                <th><?php echo $message1 ?></th>
            </tr>
        <?php endif; ?>
        <?php if ($message2) : ?>
            <tr>
                <td><?php echo $message2; ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>