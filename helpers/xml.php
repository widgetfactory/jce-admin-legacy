<?php
/**
 * @version   $Id: xml.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

class WFXMLHelper
{
	function getElement($xml, $name, $default = '')
    {
    	if (is_a($xml, 'JSimpleXML')) {
    		$element = $xml->document->getElementByPath($name);
        	return $element ? $element->data() : $default;
    	} else {
    		return (string)$xml->$name;
    	}
    }
    
    function getElements($xml, $name)
    {
        if (is_a($xml, 'JSimpleXML')) {
	        $element = $xml->document->getElementByPath($name);	
			
	        if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
	        	return $element;
	        }
        } else {
        	return $xml->$name;
        }

        return array();
    }
    
    function getAttribute($xml, $name, $default = '')
    {
    	if (is_a($xml, 'JSimpleXML')) {
    		$value = (string) $xml->document->attributes($name);
    	} else {
    		$value = (string)$xml->attributes()->$name;
    	}
    	
    	return $value ? $value : $default;
    }
    
    function getXML($file)
    {
    	// use JSimpleXML 	
    	if (!method_exists('JFactory', 'getXML')) {
    		$xml = JFactory::getXMLParser('Simple');
		
			if (!$xml->loadFile($file)) {
				unset($xml);
				return false;
			}
    	} else {
    		$xml = WFXMLElement::getXML($file);
    	}

		return $xml;
    }
}