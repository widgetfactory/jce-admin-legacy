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
 * Extension installer
 *
 * @package   JCE
 * @subpackage  Installer
 */
class WFInstallerExtension extends JObject {
	/**
	 * Constructor
	 *
	 * @param object  $parent Parent object [JInstaller instance]
	 * @return  void
	 */
	function __construct(&$parent)
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
		foreach(array(
		'name',
		'version',
		'description',
		'installfile',
		'uninstallfile'
		) as $item) {
			$this->set($item, WFXMLHelper::getElement($manifest, $item));
		}
		// attribute
		foreach(array(
		'folder',
		'extension',
		'core'
		) as $item) {
			$this->set($item, WFXMLHelper::getAttribute($manifest, $item));
		}
		// elements
		foreach(array(
		'files',
		'languages',
		'media'
		) as $item) {
			$this->set($item, WFXMLHelper::getElements($manifest, $item));
		}
		return true;
	}

	/**
	 * Install method
	 *
	 * @access  public
	 * @return  boolean True on success
	 */
	public function install()
	{
		// Get a database connector object
		$db = $this->parent->getDBO();
		// Get the extension manifest object
		$manifest = $this->parent->getManifest();
		// setup manifest data
		$this->setManifest($manifest);
		$this->parent->set('name', $this->get('name'));
		$this->parent->set('version', $this->get('version'));
		$this->parent->set('message', $this->get('description'));
		$plugin = $this->get('plugin');
		$folder = $this->get('folder');
		$extension = $this->get('extension');
		
	    if (version_compare($this->version, '2.0.0', '<')) {
			$this->parent->abort(WFText::_('WF_INSTALLER_INCORRECT_VERSION'));
			return false;
		}
		
		if(!empty($folder)) {
			$this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'extensions' . DS . $folder);
		} else {
			$this->parent->abort(WFText::_('WF_INSTALLER_EXTENSION_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_NO_EXTENSION_FOLDER'));
		}
		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Set overwrite flag if not set by Manifest
		$this->parent->setOverwrite(true);
		// If the extension directory does not exist, lets create it
		$created = false;
		if(!file_exists($this->parent->getPath('extension_root'))) {
			if(!$created = JFolder::create($this->parent->getPath('extension_root'))) {
				$this->parent->abort(WFText::_('WF_INSTALLER_EXTENSION_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_MKDIR_ERROR') . ' : "' . $this->parent->getPath('extension_root') . '"');
				return false;
			}
		}
		/*
		 * If we created the extension directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if($created) {
			$this->parent->pushStep( array('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
		}
		// Copy all necessary files
		if(!$this->parent->parseFiles($this->get('files'), -1)) {
			// Install failed, roll back changes
			$this->parent->abort();
			return false;
		}
		// Parse optional tags -- language files for plugins
		$this->parent->parseLanguages($this->get('languages'), 0);
		$this->parent->parseMedia($this->get('media'), 0);
		// Load the language file
		$language = JFactory::getLanguage();
		$language->load('com_jce_' . trim($plugin) . '_' . trim($extension), JPATH_SITE);
		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */
		// Lastly, we will copy the manifest file to its appropriate place.
		if(!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(WFText::_('WF_INSTALLER_EXTENSION_INSTALL') . ' : ' . WFText::_('WF_INSTALLER_SETUP_COPY_ERROR'));
			return false;
		}
		// post-install
		$this->addIndexfiles();
		return true;
	}
	
	/**
	* Add index.html files to each folder
	* @access private
	*/
	private function addIndexfiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		// get the base file
		$file = WF_ADMINISTRATOR . DS . 'index.html';
		$path = $this->parent->getPath('extension_root');
		if(is_file($file) && is_dir($path)) {
			
			JFile::copy($file, $path . DS . basename($file));
			
			// admin component
			$folders = JFolder::folders($path, '.', true, true);
			foreach($folders as $folder) {
				JFile::copy($file, $folder . DS . basename($file));
			}
		}
	}

	/**
	 * Uninstall method
	 *
	 * @access  public
	 * @param int   $id  The id of the extension to uninstall
	 * @return  boolean True on success
	 */
	public function uninstall($id)
	{
		// Initialize variables
		$retval = true;
		$id = explode('.', $id);
		if(count($id) < 2) {
			JError::raiseWarning(100, WFText::_('WF_INSTALLER_EXTENSION_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_EXTENSION_FIELD_EMPTY'));
			return false;
		}
		$folder = '';
		$extension = '';
		if(count($id) > 2) {
			$plugin = $id[0];
			$folder = $id[1];
			$extension = $id[2];
		} else {
			$plugin = null;
			$folder = $id[0];
			$extension = $id[1];
		}
		$this->parent->set('name', $extension);
		// Get the extension folder so we can properly build the plugin path
		if(trim($extension) == '') {
			JError::raiseWarning(100, WFText::_('WF_INSTALLER_EXTENSION_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_EXTENSION_FIELD_EMPTY'));
			return false;
		}
		if($plugin) {
			// Set the plugin root path
			$this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'tiny_mce' . DS . 'plugins' . DS . $plugin . DS . 'extensions' . DS . $folder);
		} else {
			$this->parent->setPath('extension_root', JPATH_COMPONENT_SITE . DS . 'editor' . DS . 'extensions' . DS . $folder);
		}
		$manifest = $this->parent->getPath('extension_root') . DS . $extension . '.xml';
		if(file_exists($manifest)) {
			$xml = WFXMLHelper::getXML($manifest);
			if(!$this->setManifest($xml)) {
				JError::raiseWarning(100, WFText::_('WF_INSTALLER_EXTENSION_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_MANIFEST_INVALID'));
			}
			$this->parent->set('name', WFText::_($this->get('name')));
			$this->parent->set('version', $this->get('version'));
			$this->parent->set('message', $this->get('description'));
			// can't remove a core plugin
			if($this->get('core') == 1) {
				JError::raiseWarning(100, WFText::_('WF_INSTALLER_EXTENSION_UNINSTALL') . ' : ' . JText::sprintf('WF_INSTALLER_WARNCOREEXTENSION', WFText::_($this->get('name'))));
				return false;
			}
			// Remove the extension files
			$this->parent->removeFiles($this->get('files'), -1);
			// Remove all media and languages as well
			$this->parent->removeFiles($this->get('languages'), 0);
			$this->parent->removeFiles($this->get('media'), 0);
			JFile::delete($manifest);
		} else {
			JError::raiseWarning(100, WFText::_('WF_INSTALLER_EXTENSION_UNINSTALL') . ' : ' . WFText::_('WF_INSTALLER_CUSTOM_UNINSTALL_ERROR'));
			return false;
		}
		return $retval;
	}

}
