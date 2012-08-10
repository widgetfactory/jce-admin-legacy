<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2012 Ryan Demmer. All rights reserved.
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

// Register the element class with the loader.
JLoader::register('WFElement', dirname(__FILE__) . '/element.php');

class WFParameter extends JRegistry {
    
    /**
     * @var    object  The params data object
     */
    protected $_data    = null;
    
    /**
     * @var    array  The params keys array
     */
    protected $_key     = null;

    /**
     * @var    object  The XML params element
     * @since  2.2.5
     */
    protected $_xml = null;

    /**
     * @var    array  Loaded elements
     * @since  2.2.5
     */
    protected $_elements = array();
    
    protected $_control     = 'params';

    /**
     * @var    array  Directories, where element types can be stored
     * @since  2.2.5
     */
    protected $_elementPath = array();

    function __construct($data = null, $path = '', $keys = null, $config = array()) {
        parent::__construct('_default');
        
        if (array_key_exists('control', $config)) {
            $this->_control = $config['control'];
        }

        // Set base path.
        $this->addElementPath(dirname(dirname(__FILE__)) . '/elements');
        // add joomla paths
        $this->addElementPath(JPATH_LIBRARIES . '/html/parameter/element');

        /*if ($data = trim($data)) {
            $this->loadString($data);
        }*/

        if ($path) {
            $this->loadSetupFile($path);
        }

        //$this->_raw = $data;

        $this->_data = new StdClass();

        if ($data) {
            if (!is_object($data)) {
                $data = json_decode($data);
            }

            if ($keys) {
                if (!is_array($keys)) {
                    $keys = explode('.', $keys);
                }

                $this->_key = $keys;

                foreach ($keys as $key) {
                    $data = isset($data->$key) ? $data->$key : $data;
                }
            }

            $this->bindData($this->_data, $data);
        }
    }

    /**
     * Loads an XML setup file and parses it.
     *
     * @param   string  $path  A path to the XML setup file.
     *
     * @return  object
     * @since   2.2.5
     */
    public function loadSetupFile($path) {
        $result = false;

        if ($path) {
            /*$xml = JFactory::getXMLParser('Simple');

            if ($xml->loadFile($path)) {
                if ($params = $xml->document->params) {
                    foreach ($params as $param) {
                        $this->setXML($param);
                        $result = true;
                    }
                }
            }*/
            
            $controls = explode(':', $this->_control);

            if ($xml = WFXMLElement::getXML($path)) {                
                $params = $xml;
                
                foreach($controls as $control) {
                   $params = $params->$control;
               }
                
                //if ($params = $xml->$control) {
                    foreach ($params as $param) {
                        $this->setXML($param);
                        $result = true;
                    }
                //}
            }
            
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Sets the XML object from custom XML files.
     *
     * @param   JSimpleXMLElement  &$xml  An XML object.
     *
     * @return  void

     * @since   2.2.5
     */
    public function setXML(&$xml) {
        if (is_object($xml)) {                        
            if ($group = (string)$xml->attributes()->group) { 
                $this->_xml[$group]     = $xml;
            } else {
                $this->_xml['_default'] = $xml;
            }

            if ($dir = (string)$xml->attributes()->addpath) {
                $this->addElementPath(JPATH_ROOT . $dir);
            }
        }
    }

    /**
     * Add a directory where JParameter should search for element types.
     *
     * You may either pass a string or an array of directories.
     *
     * JParameter will be searching for a element type in the same
     * order you added them. If the parameter type cannot be found in
     * the custom folders, it will look in
     * JParameter/types.
     *
     * @param   mixed  $path  Directory (string) or directories (array) to search.
     *
     * @return  void

     * @since   2.2.5
     */
    public function addElementPath($path) {
        // Just force path to array.
        settype($path, 'array');

        // Loop through the path directories.
        foreach ($path as $dir) {
            // No surrounding spaces allowed!
            $dir = trim($dir);

            // Add trailing separators as needed.
            if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
                // Directory
                $dir .= DIRECTORY_SEPARATOR;
            }

            // Add to the top of the search dirs.
            array_unshift($this->_elementPath, $dir);
        }
    }

    /**
     * Loads an element type.
     *
     * @param   string   $type  The element type.
     * @param   boolean  $new   False (default) to reuse parameter elements; true to load the parameter element type again.
     *
     * @return  object
     * @since   2.2.5
     */
    public function loadElement($type, $new = false) {
        $signature = md5($type);

        if ((isset($this->_elements[$signature]) && !($this->_elements[$signature] instanceof __PHP_Incomplete_Class)) && $new === false) {
            return $this->_elements[$signature];
        }

        $elementClass = 'WFElement' . $type;
        
        if (!class_exists($elementClass)) {
            if (isset($this->_elementPath)) {
                $dirs = $this->_elementPath;
            } else {
                $dirs = array();
            }

            $file = JFilterInput::getInstance()->clean(str_replace('_', DS, $type) . '.php', 'path');

            jimport('joomla.filesystem.path');
            if ($elementFile = JPath::find($dirs, $file)) {
                include_once $elementFile;
            } else {
                $false = false;
                return $false;
            }
        }

        if (!class_exists($elementClass)) {
            $false = false;
            return $false;
        }

        $this->_elements[$signature] = new $elementClass($this);

        return $this->_elements[$signature];
    }

    /**
     * Method to recursively bind data to a parent object.
     *
     * @param	object	$parent	The parent object on which to attach the data values.
     * @param	mixed	$data	An array or object of data to bind to the parent object.
     *
     * @return	void
     * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
     */
    public function bindData(&$parent, $data) {
        // Ensure the input data is an array.
        if (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            $data = (array) $data;
        }

        foreach ($data as $k => $v) {
            if (self::is_assoc($v) || is_object($v)) {
                $parent->$k = new stdClass();
                $this->bindData($parent->$k, $v);
            } else {
                $parent->$k = $v;
            }
        }
    }

    /**
     * Return the number of parameters in a group.
     *
     * @param   string  $group  An optional group. The default group is used if not supplied.
     *
     * @return  mixed  False if no params exist or integer number of parameters that exist.

     * @since   2.2.5
     */
    public function getNumParams($group = '_default') {
        if (!isset($this->_xml[$group]) || !count($this->_xml[$group]->children())) {
            return false;
        } else {
            return count($this->_xml[$group]->children());
        }
    }

    /**
     * Get the number of params in each group.
     *
     * @return  array  Array of all group names as key and parameters count as value.

     * @since   2.2.5
     */
    public function getGroups() {
        if (!is_array($this->_xml)) {

            return false;
        }

        $results = array();
        foreach ($this->_xml as $name => $group) {
            $results[$name] = $this->getNumParams($name);
        }
        return $results;
    }

    public function getAll($name = '') {
        $results = array();

        if ($name) {
            $groups = array($name => $this->getNumParams($name));
        } else {
            $groups = $this->getGroups();
        }

        foreach ($groups as $group => $num) {
            if (!isset($this->_xml[$group])) {
                return null;
            }

            $data = new StdClass();

            foreach ($this->_xml[$group]->children() as $param) {
                $key = $param->attributes()->name;
                $value = $this->get($key, $param->attributes()->default);

                $data->$key = $value;
            }

            $results[$group] = $data;
        }

        if ($name) {
            return $results[$name];
        }

        return $results;
    }

    private function isEmpty($value) {
        return (is_string($value) && $value == "") || (is_array($value) && empty($value));
    }

    /**
     * Get a parameter value.
     *
     * @param	string	Registry path (e.g. editor.width)
     * @param   string	Optional default value, returned if the internal value is null.
     * @return	mixed	Value of entry or null
     * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
     */
    public function get($path, $default = '', $allowempty = true) {
        // set default value as result	
        $result = $default;

        // Explode the registry path into an array
        $nodes = is_array($path) ? $path : explode('.', $path);

        // Initialize the current node to be the registry root.
        $node = $this->_data;
        $found = false;
        // Traverse the registry to find the correct node for the result.
        foreach ($nodes as $n) {
            if (isset($node->$n)) {
                $node = $node->$n;
                $found = true;
            } else {
                $found = false;
                break;
            }
        }

        if ($found) {
            $result = $node;
            if ($allowempty === false) {
                if (self::isEmpty($result)) {
                    $result = $default;
                }
            }
        }

        /* if (is_numeric($result)) {
          $result = intval($result);
          } */

        return $result;
    }

    /**
     * Render all parameters
     *
     * @access	public
     * @param	string	The name of the control, or the default text area if a setup file is not found
     * @return	array	Array of all parameters, each as array Any array of the label, the form element and the tooltip
     * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
     */
    public function getParams($name = 'params', $group = '_default') {
        if (!isset($this->_xml[$group])) {
            return false;
        }
        
        $results    = array();
        $parent     = $this->_xml[$group]->attributes()->parent;

        foreach ($this->_xml[$group]->children() as $param) {            
            $results[] = $this->getParam($param, $name, $group, $parent);

            // get sub-parameters
            if ($param->attributes()->parameters) {
                jimport('joomla.filesystem.folder');

                // load manifest files for extensions
                $files = JFolder::files(JPATH_SITE . DS . $param->attributes()->parameters, '\.xml$', false, true);

                // get the base key for the parameter
                $keys = explode('.', $param->attributes()->name);

                foreach ($files as $file) {
                    $key = $keys[0] . '.' . basename($file, '.xml');
                    $results[] = new WFParameter($this->_data, $file, $key);
                }
            }
        }

        return $results;
    }

    /**
     * Render a parameter type
     *
     * @param	object	A param tag node
     * @param	string	The control name
     * @return	array	Any array of the label, the form element and the tooltip
     * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
     */
    public function getParam(&$node, $control_name = 'params', $group = '_default', $parent = '') {
        //get the type of the parameter
        $type = $node->attributes()->type;

        $element = $this->loadElement($type);

        // error happened
        if ($element === false) {
            $result = array();
            $result[0] = $node->attributes()->name;
            $result[1] = WFText::_('Element not defined for type') . ' = ' . $type;
            $result[5] = $result[0];
            return $result;
        }

        $key = $node->attributes()->name;

        if ($node->attributes()->group) {
            $key = $node->attributes()->group . '.' . $node->attributes()->name;
        }

        // get value
        $value = $this->get($key, $node->attributes()->default);

        // get value if value is object or has parent
        if (is_object($value) || $parent) {
            $group = $parent ? $parent . '.' . $group : $group;

            $value = $this->get($group . '.' . $node->attributes()->name, $node->attributes()->default);
        }

        return $element->render($node, $value, $control_name);
    }

    private function _cleanAttribute($matches) {
        return $matches[1] . '="' . preg_replace('#([^a-z0-9_-]+)#i', '', $matches[2]) . '"';
    }

    public function render($name = 'params', $group = '_default') {
        $params = $this->getParams($name, $group);
        $html = '<ul class="adminformlist">';

        foreach ($params as $item) {
            //if (is_a($item, 'WFParameter')) {
            if ($item instanceof WFParameter) {

                foreach ($item->getGroups() as $group => $num) {
                    $label = $group;
                    $class = '';
                    $parent = '';

                    $xml = $item->_xml[$group];

                    if ($xml->attributes()->parent) {
                        $parent     = '[' . $xml->attributes()->parent . '][' . $group . ']';
                        $class      = ' class="' . $xml->attributes()->parent . '"';
                        $label      = $xml->attributes()->parent . '_' . $group;
                    }

                    $html .= '<div data-type="' . $group . '"' . $class . '>';
                    $html .= '<h4>' . WFText::_('WF_' . strtoupper($label) . '_TITLE') . '</h4>';
                    //$html .= $item->render($name . '[' . $parent . '][' . $group . ']', $group);
                    $html .= $item->render($name . $parent, $group);
                    $html .= '</div>';
                }
            } else {
                $label = preg_replace_callback('#(for|id)="([^"]+)"#', array($this, '_cleanAttribute'), $item[0]);
                $element = preg_replace_callback('#(id)="([^"]+)"#', array($this, '_cleanAttribute'), $item[1]);

                $html .= '<li>' . $label . $element;
            }
        }

        $html .= '</li></ul>';

        return $html;
    }

    /**
     * Check if a parent attribute is set. If it is, this parameter groups is included by the parent
     */
    public function hasParent() {
        foreach ($this->_xml as $name => $group) {
            if ($group->attributes()->parent) {
                return true;
            }
        }

        return false;
    }

    public static function mergeParams($params1, $params2, $toObject = true) {
        $merged = $params1;

        foreach ($params2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeParams($merged[$key], $value);
            } else {
                if ($value !== '') {
                    $merged[$key] = $value;
                }
            }
        }

        if ($toObject) {
            return self::array_to_object($merged);
        }

        return $merged;
    }

    /**
     * Method to determine if an array is an associative array.
     *
     * @param	array		An array to test.
     * @return	boolean		True if the array is an associative array.
     * @link	http://www.php.net/manual/en/function.is-array.php#98305
     */
    private static function is_assoc($array) {
        return (is_array($array) && (count($array) == 0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))))));
    }

    /**
     * Convert an associate array to an object
     * @param array Associative array
     */
    public static function array_to_object($array) {
        $object = new StdClass();

        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? self::array_to_object($value) : $value;
        }

        return $object;
    }

}
