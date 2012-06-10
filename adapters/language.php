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

defined('JPATH_BASE') or die('RESTRICTED');

/**
 * Language installer
 *
 * @package		JCE
 * @subpackage	Installer
 * @since		1.5
 */
class WFInstallerLanguage extends JObject
{
    /**
     * Constructor
     *
     * @param	object	$parent	Parent object [JInstaller instance]
     * @return	void
     */
    function __construct( & $parent)
    {
        $this->parent = $parent;
    }
    
    /**
    * Setup manifest data
    * @param object $manifest
    */
	function setManifest($manifest)
	{		
        // element
        foreach (array(
            'name',
            'version',
            'description',
        	'tag'
        ) as $item) {
            $this->set($item, WFXMLHelper::getElement($manifest, $item));
        }
        
        // elements
        foreach (array(
            'administration',
            'site',
            'tinymce'
        ) as $item) {
            $this->set($item, WFXMLHelper::getElements($manifest, $item));
        }
        
        return true;
	}

    /**
     * Install method
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function install()
    {        
        // Get the extension manifest object
        $manifest = $this->parent->getManifest();
        // setup manifest data
        $this->setManifest($manifest);
        
        $this->parent->set('name', 		$this->get('name'));
        $this->parent->set('version', 	$this->get('version'));
        $this->parent->set('message', 	$this->get('description'));

        // Check language tag - if we didn't, we may be trying to install from an older language package
        if (!$this->get('tag')) {
            $this->parent->abort(WFText::_('WF_INSTALLER_LANGUAGE_INSTALL').' : '.WFText::_('WF_INSTALLER_LANGUAGE_NO_TAG'));
            return false;
        }
        
        $folder = $this->get('tag');

        // Set the installation target paths
        $this->parent->setPath('extension_site', JPATH_SITE . DS . "language" . DS . $folder);
        $this->parent->setPath('extension_administrator', JPATH_ADMINISTRATOR . DS . "language" . DS . $folder);
        
        // Set overwrite flag if not set by Manifest
        if (!$this->parent->getOverwrite()) {
            $this->parent->setOverwrite(true);
        }

        // Copy admin files
        if ($this->parent->parseFiles($this->get('administration'), 1) === false) {
            $this->parent->abort();
			return false;
        }
        
    	// Copy site files
        if ($this->parent->parseFiles($this->get('site')) === false) {
            $this->parent->abort();
            return false;
        }
        
    	// Copy tinymce files
    	$this->parent->setPath('extension_site', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'tiny_mce');

        if ($this->parent->parseFiles($this->get('tinymce')) === false) {
            $this->parent->abort();
            return false;
        }
		
		$this->addIndexfiles($this->parent->getPath('site'));

        // Set path back to site for manifest
        $this->parent->setPath('extension_site', JPATH_SITE . DS . "language" . DS . $folder);
        // Lastly, we will copy the manifest file to its appropriate place.
        if (!$this->parent->copyManifest(0)) {
            // Install failed, rollback changes
            $this->parent->abort(WFText::_('WF_INSTALLER_LANGUAGE_INSTALL').' : '.WFText::_('WF_INSTALLER_SETUP_COPY_ERROR'));
            return false;
        }

        return true;
    }
	
    /**
    * Add index.html files to each folder
    * @access private
    */
	private function addIndexfiles($path)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		// get the base file
		$file = WF_ADMINISTRATOR . DS . 'index.html';
		
		if (is_file($file) && is_dir($path)) {
			
			JFile::copy($file, $path . DS . basename($file));
			
			// admin component
			$folders = JFolder::folders($path, '.', true, true);
			
			foreach ($folders as $folder) {
				JFile::copy($file, $folder . DS . basename($file));
			}
		}
	}

    /**
     * Uninstall method
     *
     * @access	public
     * @param	string	$tag		The tag of the language to uninstall
     * @return	mixed	Return value for uninstall method in component uninstall file
     */
    function uninstall($tag)
    {
        // Set defaults
		$this->parent->set('name', 		$tag);
		$this->parent->set('version', 	'');
		
		// Clean tag
		$tag = trim($tag);
		
		$path = JPATH_SITE.DS.'language'.DS.$tag;
		
        if (!JFolder::exists($path)) {
            JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL').' : '.WFText::_('WF_INSTALLER_LANGUAGE_PATH_EMPTY'));
            return false;
        }

        // Because JCE languages don't have their own folders we cannot use the standard method of finding an installation manifest
        $manifest = $path.DS.$tag.'.com_jce.xml';
        
        if (file_exists($manifest)) {
        	$xml = WFXMLHelper::getXML($manifest);
            
        	if (!$this->setManifest($xml)) {
            	JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL'). ' : '.WFText::_('WF_INSTALLER_MANIFEST_INVALID'));
            }

            // Set the installation target paths
            $this->parent->setPath('extension_site', $path);
            $this->parent->setPath('extension_administrator', JPATH_ADMINISTRATOR.DS."language".DS.$tag);

            if (!$this->parent->removeFiles($this->get('site'))) {
                JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL').' : '.WFText::_('WF_INSTALL_DELETE_FILES_ERROR'));
                return false;
            }
            if (!$this->parent->removeFiles($this->get('administration'), 1)) {
                JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL').' : '.WFText::_('WF_INSTALL_DELETE_FILES_ERROR'));
                return false;
            }

            $this->parent->setPath('extension_site', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'tiny_mce');
            
            if (!$this->parent->removeFiles($this->get('tinymce'))) {
                JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL').' : '.WFText::_('WF_INSTALL_DELETE_FILES_ERROR'));
                return false;
            }
            JFile::delete($manifest);
        } else {
            JError::raiseWarning(100, WFText::_('WF_INSTALLER_LANGUAGE_UNINSTALL').' : '.WFText::_('WF_INSTALLER_MANIFEST_ERROR'));
            return false;
        }
        return true;
    }
}
