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
<form action="index.php" method="post" name="adminForm">
    <div id="jce">
		<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<li class="ui-state-default ui-corner-top ui-state-active tooltip" title="<?php echo JText :: _('WF_PROFILES_SETUP'). '::'. JText :: _('WF_PROFILES_SETUP_DESC');?>"><a href="#tabs-setup"><?php echo JText :: _('WF_PROFILES_SETUP');?></a></li>
				<li class="ui-state-default ui-corner-top tooltip" title="<?php echo JText :: _('WF_PROFILES_FEATURES'). '::'. JText :: _('WF_PROFILES_FEATURES_DESC');?>"><a href="#tabs-features"><?php echo JText :: _('WF_PROFILES_FEATURES');?></a></li>
				<li class="ui-state-default ui-corner-top tooltip" title="<?php echo JText :: _('WF_PROFILES_EDITOR_PARAMETERS'). '::'. JText :: _('WF_PROFILES_EDITOR_PARAMETERS_DESC');?>"><a href="#tabs-editor"><?php echo JText :: _('WF_PROFILES_EDITOR_PARAMETERS');?></a></li>
				<li class="ui-state-default ui-corner-top tooltip" title="<?php echo JText :: _('WF_PROFILES_PLUGIN_PARAMETERS'). '::'. JText :: _('WF_PROFILES_PLUGIN_PARAMETERS_DESC');?>"><a href="#tabs-plugins"><?php echo JText :: _('WF_PROFILES_PLUGIN_PARAMETERS');?></a></li>
			</ul>
			<div id="tabs-setup" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
				<?php echo $this->loadTemplate('setup');?>
			</div>
			<div id="tabs-features" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
				<?php echo $this->loadTemplate('features');?>
			</div>
			<div id="tabs-editor" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
				<ul>
				<?php 				
				foreach($this->profile->editor_groups as $name => $group) :
					echo '<li><a href="#tabs-editor-'. $name.'"><span>'. WFText::_('WF_PROFILES_EDITOR_' . strtoupper($name)). '</span></a></li>';
				endforeach;?>
				</ul>
				<?php echo $this->loadTemplate('editor');?>
			</div>
			<div id="tabs-plugins" class="defaultSkin ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
				<ul>
				<?php
				// Build tabs
				foreach ($this->plugins as $plugin) :
					if ($plugin->editable && file_exists(WF_EDITOR_PLUGINS.DS.$plugin->name.DS.$plugin->name.'.xml')) :
						if ($plugin->core == 0) {
							// Load Language for plugin
							$language = JFactory::getLanguage();
							$language->load('com_jce_' . $plugin->name, JPATH_SITE);
						}
					
						$icon 	= ''; 
						$class 	= '';
						if ($plugin->icon) :
							$icon = $this->model->getIcon($plugin);
						endif;
						
						$class = in_array($plugin->name, explode(',', $this->profile->plugins)) ? 'ui-state-default' : 'ui-state-disabled';
						
						echo '<li class="' . $class . '"><a href="#tabs-plugin-'. $plugin->name.'">'. $icon .'<span class="label">'. WFText::_($plugin->title). '</span></a></li>';
            		endif;
				endforeach;?>
				</ul>
				<?php echo $this->loadTemplate('plugin');?>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_jce" />
	<input type="hidden" name="id" value="<?php echo $this->profile->id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->profile->id; ?>" />
    <input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>