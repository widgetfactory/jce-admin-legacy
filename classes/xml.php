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

class WFXMLElement extends SimpleXMLElement
{
	
	/**
	 * Reads a XML file.
	 *
	 * @param string  $data   Full path and file name.
	 * @param boolean $isFile true to load a file | false to load a string.
	 *
	 * @return mixed WFXMLElement on success | false on error.
	 * @todo This may go in a separate class - error reporting may be improved.
	 */
	public static function getXML($data, $isFile = true)
	{
		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile) {
			// Try to load the xml file
			$xml = simplexml_load_file($data, 'WFXMLElement');
		}
		else {
			// Try to load the xml string
			$xml = simplexml_load_string($data, 'WFXMLElement');
		}

		if (empty($xml)) {
			// There was an error
			JError::raiseWarning(100, JText::_('ERROR_XML_LOAD'));

			if ($isFile) {
				JError::raiseWarning(100, $data);
			}

			foreach (libxml_get_errors() as $error)
			{
				JError::raiseWarning(100, 'XML: '.$error->message);
			}
		}

		return $xml;
	}
	
	/**
	 * Get the name of the element.
	 *
	 * Warning: don't use getName() as it's broken up to php 5.2.3
	 *
	 * @return	string
	 */
	public function name()
	{
		if (version_compare(phpversion(), '5.2.3', '>')) {
			return (string) $this->getName();
		}

		// Workaround php bug number 41867, fixed in 5.2.4
		return (string) $this->aaa->getName();
	}

	/**
	 * Legacy method to get the element data.
	 *
	 * @return		string
	 * @deprecated	1.6 - Feb 5, 2010
	 */
	public function data()
	{
		return (string) $this;
	}

	/**
	 * Legacy method gets an elements attribute by name.
	 *
	 * @param		string
	 * @return		string
	 * @deprecated	1.6 - Feb 5, 2010
	 */
	public function getAttribute($name)
	{
		return (string) $this->attributes()->$name;
	}
}