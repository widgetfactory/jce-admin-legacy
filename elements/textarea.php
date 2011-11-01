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
 * Renders a textarea element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementTextarea extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Textarea';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$attribs 	= ' ';
		
		$attributes = array(
			'placeholder'	=> ''
		);
		
		foreach ($attributes as $k => $v) {
			$av = $node->attributes($k);
			if ($av || $v) {
				$v = !$av ? $v : $av;
				$attribs .= ' '.$k.'="'.$v.'"';
			}
		}
		
		$rows = $node->attributes('rows');
		$cols = $node->attributes('cols');
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		// convert <br /> tags so they are not visible when editing
		$value = str_replace('<br />', "\n", $value);

		return '<textarea name="'.$control_name.'['.$name.']" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'"'.$attribs.'>'.$value.'</textarea>';
	}
}
?>