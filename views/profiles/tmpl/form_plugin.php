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

$count = 0;

foreach ($this->plugins as $plugin) :
	$path     = WF_EDITOR_PLUGINS . DS . $plugin->name;
	$manifest = $path . DS . $plugin->name . '.xml';
	
	if ($plugin->type == 'plugin' && $plugin->editable && file_exists($manifest)) :
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$name   = trim($plugin->name);
		$params = new WFParameter($this->profile->params, $manifest, $plugin->name);
		
		// set element paths
		$params->addElementPath(JPATH_COMPONENT . DS . 'elements');
		$params->addElementPath(JPATH_COMPONENT_SITE . DS . 'elements');
		$params->addElementPath(WF_EDITOR . DS . 'elements');
		
		// set plugin specific elements
		if (JFolder::exists($path . DS . 'elements')) {
			$params->addElementPath($path . DS . 'elements');
		}

		$class  = in_array($plugin->name, explode(',', $this->profile->plugins)) ? '' : 'ui-tabs-hidden';
		$groups = $params->getGroups();
		
		if (count($groups)) :
			$count++;
			?>
			<div id="tabs-plugin-<?php echo $plugin->name;?>" data-name="<?php echo $plugin->name;?>" class="<?php echo $class;?>">
				<h2><?php echo WFText::_($plugin->title);?></h2>
				<?php
				// Draw parameters
				foreach ($groups as $group => $num) :
					echo '<fieldset class="adminform panelform"><legend>' . WFText::_('WF_PROFILES_PLUGINS_' . strtoupper($group)) . '</legend>';
					echo '<p>' . WFText::_('WF_PROFILES_PLUGINS_' . strtoupper($group) . '_DESC') . '</p>';
					//echo $params->render('params[' . $plugin->name . '][' . $group . ']', $group);
					echo $params->render('params[' . $plugin->name . ']', $group);
					echo '</fieldset>';
				endforeach;
				// Get extensions supported by this plugin
				$extensions = $this->model->getExtensions($plugin->name);
				
				foreach ($extensions as $extension) :
					// get extension xml file
					$file = $extension->manifest;
					if ($extension->core == 0) {
						// Load extension language file
						$language = JFactory::getLanguage();
						$language->load('com_jce_' . $extension->folder . '_' . trim($extension->extension), JPATH_SITE);
					}

					if (JFile::exists($file)) :									
						// get params for plugin
						$key 	= $plugin->name . '.' . $extension->type . '.' . $extension->extension;
						$params = new WFParameter($this->profile->params, $file, $key);
				
						// add element paths
						$params->addElementPath(JPATH_COMPONENT . DS . 'elements');
						$params->addElementPath(JPATH_COMPONENT_SITE . DS . 'elements');
						$params->addElementPath(WF_EDITOR . DS . 'elements');

						// render params
						if (!$params->hasParent()) :
							echo '<fieldset class="adminform panelform"><legend>' . WFText::_($extension->name) . '</legend>';
							echo '<p>' . WFText::_($extension->description) . '</p>';
							foreach ($params->getGroups() as $group => $num) :
								echo $params->render('params[' . $plugin->name . ']['. $extension->type .'][' . $group . ']', $group);
							endforeach;
							echo '</fieldset>';
						endif;
					endif;
				endforeach;
				?>
			</div>
			<?php
		endif;
	endif;
endforeach;

if (!$count) {
	echo WFText::_('WF_PROFILES_NO_PLUGINS');
}
?>