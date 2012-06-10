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

// load base model
require_once (dirname(__FILE__) . DS . 'model.php');

class WFModelPlugins extends WFModel {

    function getCommands() {
        //$xml  = JFactory::getXMLParser('Simple');
        $file = dirname(__FILE__) . DS . 'commands.xml';
        $xml = WFXMLElement::getXML($file);

        $commands = array();

        if ($xml) {
            //$elements = WFXMLHelper::getElements($xml, 'commands');

            foreach ($xml->children() as $command) {
                $name = (string) $command->name;

                if ($name) {
                    $commands[$name] = new StdClass();

                    foreach ($command->children() as $item) {
                        $key = $item->name();
                        $value = $item->data();
                        $commands[$name]->$key = $value;
                    }

                    $commands[$name]->type = 'command';
                }
            }
        }

        return $commands;
    }

    function getPlugins() {
        jimport('joomla.filesystem.folder');

        $plugins = array();

        // get core xml
        $xml = WFXMLElement::getXML(dirname(__FILE__) . DS . 'plugins.xml');

        if ($xml) {

            foreach ($xml->children() as $plugin) {
                $name = (string) $plugin->name;

                if ($name) {
                    $plugins[$name] = new StdClass();

                    foreach ($plugin->children() as $item) {
                        $key    = $item->name();
                        $value  = $item->data();

                        $plugins[$name]->$key = $value;
                    }

                    $plugins[$name]->type = 'plugin';

                    //$plugins[$name]->author = '';
                    //$plugins[$name]->version = '';
                    //$plugins[$name]->creationdate = '';
                    //$plugins[$name]->description = '';
                }
            }
        }

        unset($xml);

        // get all Plugins
        $folders = JFolder::folders(WF_EDITOR_PLUGINS, '.', false, true, array_merge(array('.svn', 'CVS'), array_keys($plugins)));

        foreach ($folders as $folder) {
            $name = basename($folder);
            $file = $folder . DS . $name . '.xml';

            if (is_file($file)) {
                $xml = WFXMLElement::getXML($folder . DS . $name . '.xml');

                if ($xml) {
                    $params = $xml->params;

                    if (!isset($plugins[$name])) {
                        $plugins[$name] = new StdClass();

                        $plugins[$name]->name = $name;

                        $plugins[$name]->title = (string) $xml->name;
                        $plugins[$name]->icon = (string) $xml->icon;

                        $editable = (int) $xml->attributes()->editable;
                        $plugins[$name]->editable = $editable ? $editable : ($params && count($params->children()) ? 1 : 0);

                        $row = (int) $xml->attributes()->row;

                        $plugins[$name]->row    = $row ? $row : 4;
                        $plugins[$name]->core   = (int) $xml->attributes()->core ? 1 : 0;
                    }

                    $plugins[$name]->author = (string) $xml->author;
                    $plugins[$name]->version = (string) $xml->version;
                    $plugins[$name]->creationdate = (string) $xml->creationDate;
                    $plugins[$name]->description = (string) $xml->description;

                    $plugins[$name]->authorUrl = (string) $xml->authorUrl;
                    $plugins[$name]->type = 'plugin';
                }
            }
        }

        return $plugins;
    }

    /**
     * Get a plugin's extensions
     * @param object $plugin
     * @return
     */
    function getExtensions() {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $extensions = array();

        // recursively get all extension files
        $files = JFolder::files(WF_EDITOR_EXTENSIONS, '\.xml$', true, true);

        foreach ($files as $file) {
            $object = new StdClass();
            $object->folder = basename(dirname($file));
            $object->manifest = $file;
            $object->plugins = array();
            $name = basename($file, '.xml');
            $object->name = $name;
            $object->description = '';
            $object->id = $object->folder . '.' . $object->name;

            $xml = WFXMLElement::getXML($file);

            if ($xml) {
                $plugins = (string) $xml->plugins;

                if ($plugins) {
                    $object->plugins = explode(',', $plugins);
                }

                $object->name = (string) $xml->name;
                $object->title = (string) $xml->name;
                $object->description = (string) $xml->description;

                $object->creationdate = (string) $xml->creationDate;
                $object->author = (string) $xml->author;
                $object->version = (string) $xml->version;
                $object->type = (string) $xml->attributes()->folder;
                $object->authorUrl = (string) $xml->authorUrl;


                $object->folder = (string) $xml->attributes()->folder;
                $object->core = (int) $xml->attributes()->core ? 1 : 0;

                if ($object->core == 0) {
                    // load language
                    $language = JFactory::getLanguage();
                    $language->load('com_jce_' . $object->folder . '_' . $name, JPATH_SITE);
                }
            }
            $object->extension = $name;
            $extensions[] = $object;
        }

        return $extensions;
    }

    /**
     * Process import data from XML file
     * @param object $file XML file
     * @param boolean $install Can be used by the package installer
     * @return
     */
    function processImport($file, $install = false) {
        return true;
    }

}