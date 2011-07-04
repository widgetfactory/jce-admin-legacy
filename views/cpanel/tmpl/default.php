<?php 
/**
 * @version		$Id: default.php 222 2011-06-11 17:32:06Z happy_noodle_boy $
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
<div id="jce" class="ui-corner-all">

	<ul id="cpanel">
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_CONFIG' ) . '::' . WFText::_( 'WF_CONFIG_DESC' );?>"><a href="index.php?option=com_jce&amp;view=config"><span class="config"></span><?php echo WFText::_( 'WF_CONFIG' );?></a></li>
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_PROFILES' ) . '::' . WFText::_( 'WF_PROFILES_DESC' );?>"><a href="index.php?option=com_jce&amp;view=profiles"><span class="profiles"></span><?php echo WFText::_( 'WF_PROFILES' );?></a></li>
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_INSTALL' ) . '::' . WFText::_( 'WF_INSTALLER_DESC' );?>"><a href="index.php?option=com_jce&amp;view=installer"><span class="install"></span><?php echo WFText::_( 'WF_INSTALL' );?></a></li>
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_BROWSER_TITLE' ) . '::' . WFText::_( 'WF_CPANEL_BROWSER_DESC' );?>"><a href="index.php?option=com_jce&amp;view=editor&amp;layout=plugin&amp;plugin=browser&amp;standalone=1" class="dialog modal browser" target="_blank" data-options="{'width':760,'height':480}" title="<?php echo WFText::_( 'WF_BROWSER_TITLE' );?>"><span class="browser"></span><?php echo WFText::_( 'WF_BROWSER_TITLE' );?></a></li>
	<?php if (JPluginHelper::isEnabled('system', 'jcemediabox')) :?>
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_MEDIABOX' ) . '::' . WFText::_( 'WF_MEDIABOX_DESC' );?>"><a href="index.php?option=com_jce&amp;view=mediabox"><span class="mediabox"></span><?php echo WFText::_( 'WF_MEDIABOX' );?></a></li>
	<?php endif;?>		
	</ul>
	<br style="clear:both;" />
	<ul class="adminformlist">
    	<li>
        	<span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_FORUM' ) .'::'.WFText::_( 'WF_CPANEL_FORUM_DESC' );?>">
            	<?php echo WFText::_( 'WF_CPANEL_FORUM' );?>
            </span>
            <a href="http://www.joomlacontenteditor.net/forum" target="_new">www.joomlacontenteditor.com/forum</a>
       </li>
                <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_TUTORIALS' ) .'::'.WFText::_( 'WF_CPANEL_TUTORIALS_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_TUTORIALS' );?>
                    </span>
                    <a href="http://www.joomlacontenteditor.net/support/tutorials" target="_new">www.joomlacontenteditor.com/tutorials</a>
                </li>
                <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_DOCUMENTATION' ) .'::'.WFText::_( 'WF_CPANEL_DOCUMENTATION_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_DOCUMENTATION' );?>
                    </span>
                    <a href="http://www.joomlacontenteditor.net/support/documentation" target="_new">www.joomlacontenteditor.com/documentation</a>
                </li>
                <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_FAQ' ) .'::'.WFText::_( 'WF_CPANEL_FAQ_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_FAQ' );?>
                    </span>
                    <a href="http://www.joomlacontenteditor.net/support/faq" target="_new">www.joomlacontenteditor.com/faq</a>
                </li>
                <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_LICENCE' ) .'::'.WFText::_( 'WF_CPANEL_LICENCE_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_LICENCE' );?>
                    </span>
                    <?php echo $this->model->getLicense();?>
                </li>
                 <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_VERSION' ) .'::'.WFText::_( 'WF_CPANEL_VERSION_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_VERSION' );?>
                    </span>
                    <?php echo $this->version;?>
                </li>
                 <li>
	                <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_FEED' ) .'::'.WFText::_( 'WF_CPANEL_FEED_DESC' );?>">
	                    <?php echo WFText::_( 'WF_CPANEL_FEED' );?>
	                </span>
	                <span style="display:inline-block;">
                <?php if ($this->params->get('feed', 1)) :?>
	                <ul class="newsfeed"><li><?php echo WFText::_('WF_CPANEL_FEED_NONE');?></li></ul>
                <?php else : ?>
                	<?php echo WFText::_('WF_CPANEL_FEED_DISABLED');?> :: <a title="<?php echo WFText::_('WF_PREFERENCES');?>" class="dialog modal preferences" rel="{\'width\' : 760, \'height\' : 540}" href="index.php?option=com_jce&amp;view=preferences&amp;tmpl=component">[<?php echo WFText::_('WF_CPANEL_FEED_ENABLE');?>]</a>
                <?php endif; ?>
                	</span>
                </li>
	</ul>
</div>