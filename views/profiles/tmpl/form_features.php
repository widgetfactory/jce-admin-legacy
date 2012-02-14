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
<fieldset class="first">
	<legend><?php echo WFText::_( 'WF_PROFILES_FEATURES_LAYOUT' ); ?></legend>
	<!--  Legend -->	
	<div style="margin:5px 0 0 2px;">
		<a class="dialog legend" data-options="{'width': 750, 'height': 600}" target="_blank" title="<?php echo WFText::_('WF_LEGEND_TITLE'); ?>" href="index.php?option=com_jce&tmpl=component&view=legend">
			<button id="layout-legend"><?php echo WFText::_('WF_PROFILES_LEGEND');?></button>
		</a>
	</div>
	
	<ul class="adminformlist" id="profileLayoutTable">
		<!-- Active Editor Layout -->
		<li>
			<label class="tooltip" title="<?php echo WFText::_('WF_PROFILES_FEATURES_LAYOUT_EDITOR').'::'.WFText::_('WF_PROFILES_FEATURES_LAYOUT_EDITOR_DESC'); ?>"><?php echo WFText::_('WF_PROFILES_FEATURES_LAYOUT_EDITOR'); ?></label>
			<span class="profileLayoutContainer">
				<ul class="sortableList" id="profileLayout">
				<?php
				for ($i=1; $i <= count($this->rows); $i++) : ?>
				    <li class="sortableListItem">
				        <ul class="sortableRow">
							<?php for ($x = 1; $x <= count($this->rows); $x++ ) :
						        if ($i == $x) :
						            $icons = explode(',', $this->rows[$x]);
		
									foreach ($icons as $icon) :
										if ($icon == 'spacer') :
											echo '<li class="sortableRowItem spacer"><span class="defaultSkin"><span class="mceSeparator"></span></span></li>';
										endif;
								
								    	foreach ($this->plugins as $plugin) :
											if ($plugin->icon && $plugin->name == $icon) :
								            	echo '<li class="sortableRowItem ' . $plugin->type . '" data-name="' . $plugin->name . '">' . $this->model->getIcon($plugin) . '</li>';
											endif;
								        endforeach;
									endforeach;
						        endif;
						    endfor;?>
				        </ul>
				        <span class="sortableHandle"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="margin-top:7px;"><img src="components/com_jce/media/img/spacer.gif" width="11px" height="20px" /></span></span>
				    	<span class="sortableOption"></span>
				    </li>
				<?php endfor;?>
				</ul>
				<span class="widthMarker" style="width:<?php echo $this->width;?>px;"><span><?php echo $this->width;?>px</span></span>
	 		</span>
		</li>
		<!-- Available Buttons -->
		<li>
			<label class="tooltip" title="<?php echo WFText::_('WF_PROFILES_FEATURES_LAYOUT_AVAILABLE').'::'.WFText::_('WF_PROFILES_FEATURES_LAYOUT_AVAILABLE_DESC'); ?>"><?php echo WFText::_('WF_PROFILES_FEATURES_LAYOUT_AVAILABLE'); ?></label>
			<span class="profileLayoutContainer">
				<ul class="sortableList">
				<?php 
				for ($i = 1; $i <= 5; $i++) :
				?>
				    <li class="sortableListItem">
				        <ul class="sortableRow">
				        	<?php if ($i == 5) :
								for ($x = 1; $x<=10; $x++) :?>
									<li class="sortableRowItem spacer"><span class="defaultSkin"><span class="mceSeparator"></span></span></li>
								<?php endfor;
							endif;

						    foreach ($this->plugins as $plugin) :
	                            if (!in_array($plugin->name, explode(',', implode(',', $this->rows)))) :
	                                if ($plugin->icon && $plugin->row == $i) :
	                                    echo '<li class="sortableRowItem ' . $plugin->type . '" data-name="' . $plugin->name . '">' . $this->model->getIcon($plugin) . '</li>';
						            endif;
						        endif;
						    endforeach;?>
				        </ul>
						<span class="sortableHandle"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="margin-top:7px;"><img src="components/com_jce/media/img/spacer.gif" width="11px" height="20px" /></span></span>
						<span class="sortableOption"></span>
					</li>
				<?php endfor;?>
				</ul>
			</span>	
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend><?php echo WFText::_('WF_PROFILES_FEATURES_ADDITIONAL'); ?></legend>
	<ul id="profileAdditionalFeatures" class="adminformlist">
            <?php 
            $i = 0;
            foreach ($this->plugins as $plugin) :
                 if (!$plugin->icon) :
                    if ($plugin->editable) : ?>
                        <li class="editable">
                            <label valign="top" class="key"><?php echo WFText::_($plugin->title);?></label>
							<input type="checkbox" value="<?php echo $plugin->name;?>" <?php echo in_array( $plugin->name, explode( ',', $this->profile->plugins ) ) ? 'checked="checked"' : '';?>/>
                        	<?php echo WFText::_('WF_'.strtoupper($plugin->name).'_DESC');?>
						</li>
             <?php else : ?>
                        <li>
                            <label><?php echo WFText::_($plugin->title);?></label>
							<input type="checkbox" value="<?php echo $plugin->name;?>" <?php echo in_array( $plugin->name, explode( ',', $this->profile->plugins ) ) ? 'checked="checked"' : '';?>/>
                            <?php echo WFText::_('WF_'.strtoupper($plugin->name).'_DESC');?>
                        </li>
            <?php  endif;
                endif;
                $i++;
            endforeach;?>
	</ul>
</fieldset>