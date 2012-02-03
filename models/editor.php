<?php
/**
 * @package   	JCE
 * @copyright 	Copyright � 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

defined('_JEXEC') or die('RESTRICTED');

wfimport('admin.classes.text');
wfimport('admin.helpers.xml');
wfimport('admin.helpers.extension');
wfimport('editor.libraries.classes.token');
wfimport('editor.libraries.classes.editor');

class WFModelEditor extends JModel
{
	public function buildEditor()
    {
        // get document
        $document = JFactory::getDocument();
        
        $wf = WFEditor::getInstance();
        
        // get current component
        $option 	= JRequest::getCmd('option');
        $component 	= WFExtensionHelper::getComponent(null, $option);
        
        // get default settings
        $settings = $this->getEditorSettings();
        
		// set default component id
        $component_id = 0;		
		$component_id = isset($component->extension_id) ? $component->extension_id : ($component->id ? $component->id : 0);        
        
        $version = $this->getVersion();
        
        // settings array for jce, tinymce etc
        $init = array();
        
        $profile = $wf->getProfile();
        
        if ($profile) {
			// get jqueryui theme
            $dialog_theme     = $wf->getParam('editor.dialog_theme', 'jce');
            $dialog_theme_css = JFolder::files(WF_EDITOR_LIBRARIES . DS . 'css' . DS . 'jquery' . DS . $dialog_theme, '\.css$');
            
            $settings = array_merge($settings, array(
                'theme' 		=> 'advanced',
                'component_id' 	=> $component_id,
                'plugins' 		=> $this->getPlugins()
            ), $this->getToolbar($profile->rows));
            
            // Theme and skins
            $theme = array(
                'toolbar_location' 		=> array('top','bottom', 'string'),
                'toolbar_align' 		=> array('left', 'center', 'string'),
                'statusbar_location' 	=> array('bottom','top', 'string'),
                'path' 					=> array(1, 1, 'boolean'),
                'resizing' 				=> array(1, 0, 'boolean'),
                'resize_horizontal' 	=> array(1, 1, 'boolean'),
                'resizing_use_cookie' 	=> array(1, 1, 'boolean')
            );
            
            foreach ($theme as $k => $v) {
                $settings['theme_advanced_' . $k] = $wf->getParam('editor.' . $k, $v[0], $v[1], $v[2]);
            }
			
			if (!$wf->getParam('editor.use_cookies', 1)) {
				$settings['theme_advanced_resizing_use_cookie'] = false;
			}
            
            $settings['width']  = $wf->getParam('editor.width');
            $settings['height'] = $wf->getParam('editor.height');
            
            // 'Look & Feel'
            $settings['jquery_ui'] 		= JURI::root(true) . '/components/com_jce/editor/libraries/css/jquery/' . $dialog_theme . '/' . basename($dialog_theme_css[0]);
            
            $skin                     	= explode('.', $wf->getParam('editor.toolbar_theme', 'default', 'default'));
            $settings['skin']         	= $skin[0];
            $settings['skin_variant'] 	= isset($skin[1]) ? $skin[1] : '';
            $settings['body_class']   	= $wf->getParam('editor.content_style_reset', $wf->getParam('editor.highcontrast', 0)) == 1 ? 'mceContentReset' : '';
            $settings['body_id']      	= $wf->getParam('editor.body_id', '');
            
            $settings['content_css'] 	= $this->getStyleSheets();
			
			// Editor Toggle
			$settings['toggle']			= $wf->getParam('editor.toggle', 1, 1);
			$settings['toggle_label']	= htmlspecialchars($wf->getParam('editor.toggle_label', '[show/hide]', '[show/hide]'));
			$settings['toggle_state']	= $wf->getParam('editor.toggle_state', 1, 1);  
        }// end profile
        
        //Other - user specified
        $userParams = $wf->getParam('editor.custom_config', '');
        $baseParams = array (
	    	'mode',
	        'cleanup_callback',
	        'save_callback',
	        'file_browser_callback',
	        'urlconverter_callback',
	        'onpageload',
			'oninit',
	        'editor_selector'
        );
		
        if ($userParams) {
        	$userParams = explode(';', $userParams);
            foreach ($userParams as $userParam) {
				$keys = explode(':', $userParam);
				if (!in_array(trim($keys[0]), $baseParams)) {
					$settings[trim($keys[0])] = count($keys) > 1 ? trim($keys[1]) : '';
				}
			}
		}
		
        // set compression states
	    $compress = array(
	    	'javascript' 	=> intval($wf->getParam('editor.compress_javascript', 0)),
	       	'css'			=> intval($wf->getParam('editor.compress_css', 0))
	    );

    	// create token
		$token = WFToken::getToken();
		$query = array(
			'component_id' 	=> $component_id,
			'version'		=> $version
		);
		
		$query[$token] = 1;
		
        // set compression
        if ($compress['javascript']) {
           	$document->addScript(JURI::base(true) . '/index.php?option=com_jce&view=editor&layout=editor&task=pack&component_id=' . $component_id . '&' . $token . '=1&version=' . $version);
        } else {
            $document->addScript($this->getURL(true) . '/tiny_mce/tiny_mce.js?version=' . $version);            
            // Editor
            $document->addScript($this->getURL(true) . '/libraries/js/editor.js?version=' . $version);
            // languages TODO
            //$document->addScript(JURI::base(true) . '/index.php?option=com_jce&view=editor&layout=editor&task=pack&type=language&component_id=' . $component_id . '&' . $token . '=1&version=' . $version);
        }
        // set compression
        if ($compress['css']) {
            $document->addStyleSheet(JURI::base(true) . '/index.php?option=com_jce&view=editor&layout=editor&task=pack&type=css&component_id=' . $component_id . '&' . $token . '=1&version=' . $version);
        } else {
        	// CSS
            $document->addStyleSheet($this->getURL(true) . '/libraries/css/editor.css?version=' . $version);
            // get plugin styles
        	$this->getPluginStyles($settings);
        }

		// Get all optional plugin configuration options
        $this->getPluginConfig($settings);
        
        // pass compresison states to settings
        $settings['compress'] = json_encode($compress);
        
        $output = "";
        $i      = 1;
        
        foreach ($settings as $k => $v) {
            // If the value is an array, implode!
            if (is_array($v)) {
                $v = ltrim(implode(',', $v), ',');
            }
            // Value must be set
            if ($v !== '') {
                // objects or arrays or functions or regular expression
                if (preg_match('/(\[[^\]*]\]|\{[^\}]*\}|function\([^\}]*\}|^#(.*)#$)/', $v)) {
                    // replace hash delimiters with / for javascript regular expression
                    $v = preg_replace('@^#(.*)#$@', '/$1/', $v);
                }
				// boolean
				else if (is_bool($v)) {
					$v = $v ? 'true' : 'false';
				}
                // anything that is not solely an integer
                else if (!is_numeric($v)) {
                	if (strpos($v, '"') === 0) {
						$v = '"' . trim($v, '"') . '"';
                	} else {
                		$v = '"' . str_replace('"', '\"', $v) . '"';
                	}
                }

                $output .= "\t\t\t" . $k . ": " . $v . "";
                if ($i < count($settings)) {
                    $output .= ",\n";
                }
            }
            // Must have 3 rows, even if 2 are blank!
            if (preg_match('/theme_advanced_buttons([1-3])/', $k) && $v == '') {
                $output .= "\t\t\t" . $k . ": \"\"";
                if ($i < count($settings)) {
                    $output .= ",\n";
                }
            }
            $i++;
        }
        
        $tinymce = "{\n";
        $tinymce .= preg_replace('/,?\n?$/', '', $output) . "
        }";
        
        $init[] = $tinymce;

        $document->addScriptDeclaration("\t\ttry{WFEditor.init(" . implode(',', $init) . ");}catch(e){alert(e);}");
        
        if ($profile) {
        	if ($wf->getParam('editor.callback_file')) {
            	$document->addScript(JURI::root(true) . '/' . $callbackFile);
			}
        }
    }
    
    /**
     * Get the current version
     * @return Version
     */
    function getVersion()
    {
        // Get Component xml
        $xml = JApplicationHelper::parseXMLInstallFile(WF_ADMINISTRATOR . DS . 'jce.xml');
        
        // return cleaned version number or date
        $version = preg_replace('/[^0-9a-z]/i', '', $xml['version']);
        if (!$version) {
            return date('Y-m-d', strtotime('today'));
        }
        return $version;
    }

    /**
     * Get default settings array
     * @return array
     */
    public function getEditorSettings()
    {
        wfimport('editor.libraries.classes.token');	
	
        $wf = WFEditor::getInstance();
        
        $language = JFactory::getLanguage();
        
        $settings = array(
        	'token'				=> WFToken::getToken(),
            'base_url' 			=> JURI::root(),
            'language' 			=> $wf->getLanguage(),
            //'language_load'		=> false,
            'directionality' 	=> $language->isRTL() ? 'rtl' : 'ltr',
            'theme' 			=> 'none',
            'plugins'			=> ''
        );
        
        return $settings;
    }
    
    /**
     * Return a list of icons for each JCE editor row
     *
     * @access public
     * @param string  The number of rows
     * @return The row array
     */
    private function getToolbar($toolbar)
    {
        $model = JModel::getInstance('plugins', 'WFModel');
        
        $db = JFactory::getDBO();
        
        $rows = array(
            'theme_advanced_buttons1' => '',
            'theme_advanced_buttons2' => '',
            'theme_advanced_buttons3' => ''
        );
        
        $plugins  = $model->getPlugins();
        $commands = $model->getCommands();
        
        $icons = array_merge($commands, $plugins);
        $lists = explode(';', $toolbar);
        
        $x = 0;
        for ($i = 1; $i <= count($lists); $i++) {
            $items = array();
            
            foreach (explode(',', $lists[$x]) as $item) {
                if ($item == 'spacer') {
                    $items[] = '|';
                } else {
                    if (isset($icons[$item])) {
						$items[] = $icons[$item]->icon;
                    }
                }
            }
            
            if (!empty($items)) {
            	$rows['theme_advanced_buttons' . $i] = implode(',', $items);
            }
            
            $x++;
        }
        
        return $rows;
    }
    
    /**
     * Return a list of published JCE plugins
     *
     * @access public
     * @return string list
     */
    private function getPlugins()
    {
        $db = JFactory::getDBO();
        $wf = WFEditor::getInstance();
        
        jimport('joomla.filesystem.file');
        
        $plugins = array();
        
        $profile = $wf->getProfile();
        
        if (is_object($profile)) {
            $plugins = explode(',', $profile->plugins);
            
            $plugins = array_unique(array_merge(array(
                'advlist',
            	'autolink',
            	'cleanup',
                'code',
                'format',
            	'lists',
                'tabfocus',
                'wordcount'
            ), $plugins));
            
            $compress = $wf->getParam('editor.compress_javascript', 0);
            
            foreach ($plugins as $plugin) {
                $path = WF_EDITOR_PLUGINS . DS . $plugin;
                
                $language = $wf->getLanguage();
                
                if (!JFolder::exists($path) || !JFile::exists($path . DS . 'editor_plugin.js')) {
                    $this->removeKeys($plugins, $plugin);
                }
                
                if (!$compress) {
                    if ($language != 'en') {
                        // new language file
                        $new = $path . DS . 'langs' . DS . $language . '.js';
                        // existing english file
                        $en  = $path . DS . 'langs' . DS . 'en.js';
                        
                        if (JFile::exists($en) && !JFile::exists($new)) {
							// remove plugin and throw error
                           	$this->removeKeys($plugins, $plugin);
							JError::raiseNotice('SOME_ERROR_CODE', sprintf(WFText::_('PLUGIN NOT LOADED : LANGUAGE FILE MISSING'), 'components/com_jce/editor/tiny_mce/plugins/' . $plugin . '/langs/' . $language . '.js') . ' - ' . ucfirst($plugin));
                        }
                    }
                }
            }
        }
        
        return $plugins;
    }
    
    /**
     * Get all loaded plugins config options
     *
     * @access      public
     * @param array   $settings passed by reference
     */
    function getPluginConfig(&$settings)
    {
        $plugins = $settings['plugins'];
        
        if ($plugins && is_array($plugins)) {
            foreach ($plugins as $plugin) {
                $file = WF_EDITOR_PLUGINS . DS . $plugin . DS . 'classes' . DS . 'config.php';
                
                if (file_exists($file)) {
                    require_once($file);
                    // Create class name
                    $classname = 'WF' . ucfirst($plugin) . 'PluginConfig';
                    
                	// Check class and method
                    if (class_exists($classname) && method_exists(new $classname, 'getConfig')) {
                    	call_user_func_array(array($classname, 'getConfig'), array(&$settings));
                    }
                }
            }
        }
    }
    
	/**
     * Get all loaded plugins styles
     *
     * @access      public
     * @param array   $settings passed by reference
     */
    function getPluginStyles($settings)
    {
        $plugins = $settings['plugins'];
        
        if ($plugins && is_array($plugins)) {
            foreach ($plugins as $plugin) {
                $file = WF_EDITOR_PLUGINS . DS . $plugin . DS . 'classes' . DS . 'config.php';
                
                if (file_exists($file)) {
                    require_once($file);
                    // Create class name
                    $classname = 'WF' . ucfirst($plugin) . 'PluginConfig';
                    
                    // Check class and method
                    if (class_exists($classname) && method_exists(new $classname, 'getStyles')) {
                    	call_user_func(array($classname, 'getStyles'));
                    }
                }
            }
        }
    }

    /**
     * Remove keys from an array
     *
     * @return $array by reference
     * @param arrau $array Array to edit
     * @param array $keys Keys to remove
     */
    function removeKeys(&$array, $keys)
    {
        if (!is_array($keys)) {
            $keys = array(
                $keys
            );
        }
        
        $array = array_diff($array, $keys);
        
    }
    /**
     * Add keys to an array
     *
     * @return The string list with added key or the key
     * @param string  The array
     * @param string  The keys to add
     */
    function addKeys(&$array, $keys)
    {
        if (!is_array($keys)) {
            $keys = array(
                $keys
            );
        }
        $array = array_unique(array_merge($array, $keys));
    }
    
    /**
     * Get a list of editor font families
     *
     * @return string font family list
     * @param string $add Font family to add
     * @param string $remove Font family to remove
     */
    function getEditorFonts()
    {
        $wf = WFEditor::getInstance();
        
        $add    = explode(';', $wf->getParam('editor.theme_advanced_fonts_add', ''));
        $remove = preg_split('/[;,]+/', $wf->getParam('editor.theme_advanced_fonts_remove', ''));
        
        // Default font list
        $fonts = array(
            'Andale Mono=andale mono,times',
            'Arial=arial,helvetica,sans-serif',
            'Arial Black=arial black,avant garde',
            'Book Antiqua=book antiqua,palatino',
            'Comic Sans MS=comic sans ms,sans-serif',
            'Courier New=courier new,courier',
            'Georgia=georgia,palatino',
            'Helvetica=helvetica',
            'Impact=impact,chicago',
            'Symbol=symbol',
            'Tahoma=tahoma,arial,helvetica,sans-serif',
            'Terminal=terminal,monaco',
            'Times New Roman=times new roman,times',
            'Trebuchet MS=trebuchet ms,geneva',
            'Verdana=verdana,geneva',
            'Webdings=webdings',
            'Wingdings=wingdings,zapf dingbats'
        );
        
        if (count($remove)) {
            foreach ($fonts as $key => $value) {
                foreach ($remove as $gone) {
                    if ($gone) {
                        if (preg_match('/^' . $gone . '=/i', $value)) {
                            // Remove family
                            unset($fonts[$key]);
                        }
                    }
                }
            }
        }
        foreach ($add as $new) {
            // Add new font family
            if (preg_match('/([^\=]+)(\=)([^\=]+)/', trim($new)) && !in_array($new, $fonts)) {
                $fonts[] = $new;
            }
        }
        natcasesort($fonts);
        return implode(';', $fonts);
    }
    
    /**
     * Return the current site template name
     *
     * @access public
     */
    function getSiteTemplates()
    {
        $db 	= JFactory::getDBO();
        $app 	= JFactory::getApplication();
        $id 	= 0;
        
        if ($app->isSite()) {
            $menus 	= JSite::getMenu();
            $menu 	= $menus->getActive();
            
            if ($menu) {
                $id = isset($menu->template_style_id) ? $menu->template_style_id : $menu->id;
            }
        }
        
        // Joomla! 1.5
        if (WF_JOOMLA15) {
            $query = 'SELECT menuid as id, template'
            . ' FROM #__templates_menu'
            . ' WHERE client_id = 0'
			;
            
            $db->setQuery($query);
            $templates = $db->loadObjectList();
            // Joomla! 1.6+
        } else {
            $query = 'SELECT id, template'
            . ' FROM #__template_styles'
            . ' WHERE client_id = 0'
            . ' AND home = 1'
            ;
            
            $db->setQuery($query);
            $templates = $db->loadObjectList();
        }
		
		$assigned = array();
			
		foreach ($templates as $template) {				
            if ($id == $template->id) {
               	array_unshift($assigned, $template->template);
            } else {
            	$assigned[] = $template->template;
            }
        }

        // return templates
        return $assigned;
    }
    
    function getStyleSheets($absolute = false)
    {
        jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
        
        $wf = WFEditor::getInstance();
		
		$path = '';
		$url  = '';
        
		// get templates
        $templates = $this->getSiteTemplates();
		
		foreach($templates as $template) {
			// Template CSS
        	$path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'css';
			// get the first path that exists
			if (is_dir($path)) {
				$url  = "templates/" . $template . "/css";	
				break;
			}
			
			$path = '';
		}
		
        $styles      = '';
        $stylesheets = array();
        $files       = array();
		
		if ($path) {			
			// Joomla! 1.5 standard
	        $file = 'template.css';
	        
	        $css 	= JFolder::files($path, '(base|core|template|template_css)\.css$', false, true);
			
			if (!empty($css)) {
				// use the first result
				$file 	= $css[0]; 
			}

			// check for php version
			if (JFile::exists($file . '.php')) {
				$file = $file . '.php';
	        }
	        
	        $global  = intval($wf->getParam('editor.content_css', 1));
	        $profile = intval($wf->getParam('editor.profile_content_css', 2));
	        
	        // use getParam so result is cleaned
	        $global_custom = $wf->getParam('editor.content_css_custom', '');
	        // Replace $template variable with site template name
	        $global_custom = str_replace('$template', $template, $global_custom);
	        // explode to array
	        $global_custom = explode(',', $global_custom);
	        
	        switch ($global) {
	            // Custom template css files
	            case 0:
	                $files = $global_custom;
	                break;
	            // Template css (template.css or template_css.css)
	            case 1:
	                $files[] = $url . '/' . basename($file);
	                break;
	            // Nothing, use editors default stylesheet
	            case 2:
	                $files = array();
	                break;
	        }
	        
	        $profile_custom = $wf->getParam('editor.profile_content_css_custom', '');
	        // Replace $template variable with site template name
	        $profile_custom = str_replace('$template', $template, $profile_custom);
	        // explode to array
	        $profile_custom = explode(',', $profile_custom);
	        
	        switch ($profile) {
	            // add to global config value
	            case 0:
	                $files = array_merge($files, $profile_custom);
	                break;
	            // overwrite global config value
	            case 1:
	                $files = $profile_custom;
	                break;
	            // inherit global config value
	            case 2:
	                break;
	        }
	        // get rid of duplicate css files
	        $files = array_unique($files);
	        
	        $root = $absolute ? JPATH_SITE : JURI::root(true);
	        
	        // check each file and make array of stylesheets
	        foreach ($files as $file) {
	            if ($file && JFile::exists(JPATH_SITE . DS . $file)) {
	                $stylesheets[] = $root . '/' . $file;
	            }
	        }
	        // get rid of duplicate stylesheets
	        $stylesheets = array_unique($stylesheets);
	        
	        // default editor stylesheet
	        if ($global == 2 && !count($stylesheets)) {
	            $styles = '';
	        } else {
	            if (count($stylesheets)) {
	                $styles = implode(',', $stylesheets);
	            }
	        }
		}
        
        return $styles;
    }
    
    function getURL($relative = false)
    {
        if ($relative) {
            return JURI::root(true) . '/components/com_jce/editor';
        }
        
        return JURI::root() . 'components/com_jce/editor';
    }
    
    /**
     * Pack / compress editor files
     */
    public function pack()
    {
        // check token
		WFToken::checkToken('GET') or die('RESTRICTED');
    	
    	$wf = WFEditor::getInstance();
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'packer.php');
        
        $type = JRequest::getWord('type', 'javascript');
        
        // javascript
        $packer = new WFPacker(array(
            'type' => $type
        ));
        
        $themes    = 'none';
        $plugins   = array();
        $languages = $wf->getLanguage();
        
        $suffix 		= JRequest::getWord('suffix', '');
        $component_id   = JRequest::getInt('component_id', 0);
        
        if ($wf->checkUser()) {
            $themes  = 'advanced';
            $plugins = $this->getPlugins();
        }

        $languages = explode(',', $languages);
        $themes    = explode(',', $themes);
        
        // toolbar theme
        $toolbar = explode('.', $wf->getParam('editor.toolbar_theme', 'default'));
        
        switch($type) {
        	case 'language':
        		$files = array();

        		// Add core languages
        		foreach ($languages as $language) {
        			$file = WF_EDITOR . DS . "tiny_mce/langs/" . $language . ".js";
        			if (!JFile::exists($file)) {
        				$file = WF_EDITOR . DS . "tiny_mce/langs/en.js";
        			}
        			$files[] = $file;
        		}
        		
        		// Add themes
        		foreach ($themes as $theme) {        		
        			foreach ($languages as $language) {
        				$file = WF_EDITOR . DS . "tiny_mce/themes/" . $theme . "/langs/" . $language . ".js";
        				if (!JFile::exists($file)) {
        					$file = WF_EDITOR . DS . "tiny_mce/themes/" . $theme . "/langs/en.js";
        				}
        		
        				$files[] = $file;
        			}
        		}
        		
        		// Add plugins
        		foreach ($plugins as $plugin) {        		
        			foreach ($languages as $language) {
        				$file = WF_EDITOR . DS . "tiny_mce/plugins/" . $plugin . "/langs/" . $language . ".js";
        				if (!JFile::exists($file)) {
        					$file = WF_EDITOR . DS . "tiny_mce/plugins/" . $plugin . "/langs/en.js";
        				}
        				if (JFile::exists($file)) {
        					$files[] = $file;
        				}
        			}
        		}
        		// reset type
        		$type = 'javascript';
        		
        		break;
        	case 'javascript':
        		$files = array();
        		 
        		// add core file
        		$files[] = WF_EDITOR . DS . "tiny_mce/tiny_mce" . $suffix . ".js";
        		
        		// Add core languages
        		foreach ($languages as $language) {
        			$file = WF_EDITOR . DS . "tiny_mce/langs/" . $language . ".js";
        			if (!JFile::exists($file)) {
        				$file = WF_EDITOR . DS . "tiny_mce/langs/en.js";
        			}
        			$files[] = $file;
        		}
        		
        		// Add themes
        		foreach ($themes as $theme) {
        			$files[] = WF_EDITOR . DS . "tiny_mce/themes/" . $theme . "/editor_template" . $suffix . ".js";
        		
        			foreach ($languages as $language) {
        				$file = WF_EDITOR . DS . "tiny_mce/themes/" . $theme . "/langs/" . $language . ".js";
        				if (!JFile::exists($file)) {
        					$file = WF_EDITOR . DS . "tiny_mce/themes/" . $theme . "/langs/en.js";
        				}
        		
        				$files[] = $file;
        			}
        		}
        		
        		// Add plugins
        		foreach ($plugins as $plugin) {
        			$files[] = WF_EDITOR . DS . "tiny_mce/plugins/" . $plugin . "/editor_plugin" . $suffix . ".js";
        		
        			foreach ($languages as $language) {
        				$file = WF_EDITOR . DS . "tiny_mce/plugins/" . $plugin . "/langs/" . $language . ".js";
        				if (!JFile::exists($file)) {
        					$file = WF_EDITOR . DS . "tiny_mce/plugins/" . $plugin . "/langs/en.js";
        				}
        				if (JFile::exists($file)) {
        					$files[] = $file;
        				}
        			}
        		}
        		
        		// add Editor file
        		$files[] = WF_EDITOR . DS . 'libraries' . DS . 'js' . DS . 'editor.js';
        		break;
        	case 'css':
        		$context = JRequest::getWord('context', 'editor');
        		
        		if ($context == 'content') {
        			$files = array();
        		
        			$files[] = WF_EDITOR_THEMES . DS . $themes[0] . DS . 'skins' . DS . $toolbar[0] . DS . 'content.css';
        			 
        			// get template stylesheets
        			$styles = explode(',', $this->getStyleSheets(true));
        		
        			foreach ($styles as $style) {
        				if (JFile::exists($style)) {
        					$files[] = $style;
        				}
        			}
        		
        			// load content styles dor each plugin if they exist
        			foreach ($plugins as $plugin) {
        				$content = WF_EDITOR_PLUGINS . DS . $plugin . DS . 'css' . DS . 'content.css';
        				if (JFile::exists($content)) {
        					$files[] = $content;
        				}
        			}
        		} else {
        			$files = array();
        			 
        			$files[] = WF_EDITOR_LIBRARIES . DS . 'css' . DS . 'editor.css';
        			$dialog  = $wf->getParam('editor.dialog_theme', 'jce');
        		
        			$files[] = WF_EDITOR_THEMES . DS . $themes[0] . DS . 'skins' . DS . $toolbar[0] . DS . 'ui.css';
        		
        			if (isset($toolbar[1])) {
        				$files[] = WF_EDITOR_THEMES . DS . $themes[0] . DS . 'skins' . DS . $toolbar[0] . DS . 'ui_' . $toolbar[1] . '.css';
        			}
        		
        			// get external styles from config class for each plugin
        			foreach ($plugins as $plugin) {
        				$class = WF_EDITOR_PLUGINS . DS . $plugin . DS . 'classes' . DS . 'config.php';
        				if (JFile::exists($class)) {
        					require_once($class);
        					$classname = 'WF' . ucfirst($plugin) . 'PluginConfig';
        					if (class_exists($classname) && method_exists(new $classname, 'getStyles')) {
        						$files = array_merge($files, (array)call_user_func(array($classname, 'getStyles')));
        					}
        				}
        			}
        		}
        		break;
        }
        
        $packer->setFiles($files);
        $packer->pack();
    }
    
    public function getToken($id)
    {
        return '<input type="hidden" id="wf_' . $id . '_token" name="' . WFToken::getToken() . '" value="1" />';
    }
}
?>