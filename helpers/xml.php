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

abstract class WFXMLHelper
{
	public static function getElement($xml, $name, $default = '')
    {
    	if ($xml instanceof JSimpleXML) {
    	//if (is_a($xml, 'JSimpleXML')) {
    		$element = $xml->document->getElementByPath($name);
        	return $element ? $element->data() : $default;
    	} else {
    		return (string)$xml->$name;
    	}
    }
    
    public static function getElements($xml, $name)
    {
    	if ($xml instanceof JSimpleXML) {
    	//if (is_a($xml, 'JSimpleXML')) {
	        $element = $xml->document->getElementByPath($name);	
			
	        if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
	        	return $element;
	        }
        } else {
        	return $xml->$name;
        }

        return array();
    }
    
    public static function getAttribute($xml, $name, $default = '')
    {
    	//if (is_a($xml, 'JSimpleXML')) {
    	if ($xml instanceof JSimpleXML) {
    		$value = (string) $xml->document->attributes($name);
    	} else {
    		$value = (string)$xml->attributes()->$name;
    	}
    	
    	return $value ? $value : $default;
    }
    
    public static function getXML($file)
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