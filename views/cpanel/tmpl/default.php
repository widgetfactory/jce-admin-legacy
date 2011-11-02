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
<div id="jce" class="ui-corner-all">
	<ul id="cpanel">
		<?php if ($this->model->authorize('config')) :?>
			<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_CONFIG' ) . '::' . WFText::_( 'WF_CONFIG_DESC' );?>"><a href="index.php?option=com_jce&amp;view=config"><span class="config"></span><?php echo WFText::_( 'WF_CONFIG' );?></a></li>
		<?php endif;?>
		<?php if ($this->model->authorize('profiles')) :?>
			<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_PROFILES' ) . '::' . WFText::_( 'WF_PROFILES_DESC' );?>"><a href="index.php?option=com_jce&amp;view=profiles"><span class="profiles"></span><?php echo WFText::_( 'WF_PROFILES' );?></a></li>
		<?php endif;?>
		<?php if ($this->model->authorize('installer')) :?>
			<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_INSTALL' ) . '::' . WFText::_( 'WF_INSTALLER_DESC' );?>"><a href="index.php?option=com_jce&amp;view=installer"><span class="install"></span><?php echo WFText::_( 'WF_INSTALL' );?></a></li>
		<?php endif;?>
		<?php if ($this->model->authorize('browser')) :?>
			<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_BROWSER_TITLE' ) . '::' . WFText::_( 'WF_CPANEL_BROWSER_DESC' );?>"><a href="<?php echo $this->model->getBrowserLink();?>" class="dialog browser" target="_blank" data-options="{'width':765,'height':480, 'modal':true}" title="<?php echo WFText::_( 'WF_BROWSER_TITLE' );?>"><span class="browser"></span><?php echo WFText::_( 'WF_BROWSER_TITLE' );?></a></li>
		<?php endif;?>
	<?php if (JPluginHelper::isEnabled('system', 'jcemediabox') && $this->model->authorize('mediabox')) :?>
		<li class="cpanel-icon hasTip" title="<?php echo WFText::_( 'WF_MEDIABOX' ) . '::' . WFText::_( 'WF_MEDIABOX_DESC' );?>"><a href="index.php?option=com_jce&amp;view=mediabox"><span class="mediabox"></span><?php echo WFText::_( 'WF_MEDIABOX' );?></a></li>
	<?php endif;?>		
	</ul>
	<br style="clear:both;" />
	<ul class="adminformlist">
                <li>
                    <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_SUPPORT' ) .'::'.WFText::_( 'WF_CPANEL_SUPPORT_DESC' );?>">
                        <?php echo WFText::_( 'WF_CPANEL_SUPPORT' );?>
                    </span>
                    <a href="http://www.joomlacontenteditor.net/support" target="_new">www.joomlacontenteditor.com/support</a>
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
                <?php if ($this->params->get('feed', 0) || $this->model->authorize('preferences')) :?>
	                 <li>
		                <span class="hasTip" title="<?php echo WFText::_( 'WF_CPANEL_FEED' ) .'::'.WFText::_( 'WF_CPANEL_FEED_DESC' );?>">
		                    <?php echo WFText::_( 'WF_CPANEL_FEED' );?>
		                </span>
		                <span style="display:inline-block;">
	                <?php if ($this->params->get('feed', 0)) :?>
		                <ul class="newsfeed"><li><?php echo WFText::_('WF_CPANEL_FEED_NONE');?></li></ul>
	                <?php else :?>
	                		<?php echo WFText::_('WF_CPANEL_FEED_DISABLED');?> :: <a title="<?php echo WFText::_('WF_PREFERENCES');?>" class="dialog preferences" data-options="{'width':760,'height':540,'modal':true}" href="index.php?option=com_jce&amp;view=preferences&amp;tmpl=component">[<?php echo WFText::_('WF_CPANEL_FEED_ENABLE');?>]</a>
	                <?php endif; ?>
	                	</span>
	                </li>
                <?php endif; ?>
	</ul>
</div>