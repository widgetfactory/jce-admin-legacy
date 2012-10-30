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
<div id="jce">
    <fieldset>
        <legend><?php echo WFText::_('WF_UPDATES_AVAILABLE'); ?></legend>
        <table class="table table-striped table-bordered ui-widget ui-widget-content" id="updates-list" cellspacing="1">
            <thead>
                <tr class="ui-widget-header">
                    <th width="3%"></th>
                    <th class="title">
                        <?php echo WFText::_('WF_UPDATES_NAME') ?>
                    </th>
                    <th class="title" width="20%">
                        <?php echo WFText::_('WF_UPDATES_TYPE') ?>
                    </th>
                    <th class="title" width="20%">
                        <?php echo WFText::_('WF_UPDATES_VERSION') ?>
                    </th>
                    <th class="title" width="20%">
                        <?php echo WFText::_('WF_UPDATES_PRIORITY') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="5"></td></tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset>
        <legend><?php echo WFText::_('WF_UPDATES_INFO') ?></legend>
        <div id="updates-info"></div>
    </fieldset>
    <div class="btn-group pull-right fltrgt">
        <button id="update-button" class="check btn">&nbsp;<?php echo WFText::_('WF_UPDATES_CHECK'); ?></button>
    </div>
</div>