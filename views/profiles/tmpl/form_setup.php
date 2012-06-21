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
<fieldset>
    <legend><?php echo WFText::_('WF_PROFILES_DETAILS'); ?></legend>
    <ul class="adminformlist">
        <li>
            <label for="profile_name" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_NAME') . '::' . WFText::_('WF_PROFILES_NAME_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_NAME'); ?>
            </label>
            <input class="text_area required" type="text" name="name" id="profile_name" size="35" value="<?php echo $this->profile->name; ?>" />
        </li>
        <li>
            <label for="profile_description" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_DESCRIPTION') . '::' . WFText::_('WF_PROFILES_DESCRIPTION_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_DESCRIPTION'); ?>
            </label>
            <input class="text_area" type="text" name="description" id="profile_description" size="100" value="<?php echo $this->profile->description; ?>" />
        </li>
        <li>
            <label for="profile_published" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_ENABLED') . '::' . WFText::_('WF_PROFILES_ENABLED_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_ENABLED'); ?>
            </label>
                <?php echo $this->lists['published']; ?>
        </li>
        <li>
            <label for="ordering" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_ORDERING') . '::' . WFText::_('WF_PROFILES_ORDERING_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_ORDERING'); ?>
            </label>
                <?php echo $this->lists['ordering']; ?>
        </li>
    </ul>
</fieldset>
<fieldset>
    <legend><?php echo WFText::_('WF_PROFILES_ASSIGNMENT'); ?></legend>
    <ul class="adminformlist">
        <li>
            <label for="ordering" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_AREA') . '::' . WFText::_('WF_PROFILES_AREA_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_AREA'); ?>
            </label>
                <?php echo $this->lists['area']; ?>
        </li>
        <li>
            <label for="ordering" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_COMPONENTS') . '::' . WFText::_('WF_PROFILES_COMPONENTS_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_COMPONENTS'); ?>
            </label>
            <span class="list">
                <div>
<?php echo $this->lists['components-select']; ?>
                </div>
                <div>
<?php echo $this->lists['components']; ?>
                </div>
            </span>
        </li>
        <li>
            <label for="ordering" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_GROUPS') . '::' . WFText::_('WF_PROFILES_GROUPS_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_GROUPS'); ?>
            </label>
            <span class="list">
                <div style="margin:2px 5px;"><input class="checkbox-list-toggle-all" type="checkbox" /><label><?php echo WFText::_('WF_PROFILES_TOGGLE_ALL'); ?></label></div>
<?php echo $this->lists['usergroups']; ?>
            </span>
        </li>
        <li>
            <label for="ordering" class="tooltip" title="<?php echo WFText::_('WF_PROFILES_USERS') . '::' . WFText::_('WF_PROFILES_USERS_DESC'); ?>">
<?php echo WFText::_('WF_PROFILES_USERS'); ?>
            </label>
            <span class="list">
<?php echo $this->lists['users']; ?>
                <div style="margin:5px 0 0;">
                    <a class="dialog users" id="users-add" data-options="{'width':760, 'height':540, 'modal':true}" title="<?php echo WFText::_('WF_PROFILES_USERS_ADD'); ?>" href="index.php?option=com_jce&tmpl=component&view=users">
                        <?php echo WFText::_('WF_PROFILES_USERS_ADD'); ?>
                    </a>
                    <a id="users-remove" href="javascript:;"><?php echo WFText::_('WF_PROFILES_REMOVE_USERS'); ?></a>
                </div>
            </span>
        </li>
    </ul>
</fieldset>