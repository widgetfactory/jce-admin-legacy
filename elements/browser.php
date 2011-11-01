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
 * Renders a text element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementBrowser extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Browser';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$attributes = array();

		foreach ($node->attributes() as $k => $v) {	
			if ($v != '') {
				$attributes[$k] = $v;
			}
		}
		
		/*
         * Required to avoid a cycle of encoding &
         * html_entity_decode was used in place of htmlspecialchars_decode because
         * htmlspecialchars_decode is not compatible with PHP 4
         */
        $value 					= htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);
		$attributes['class'] 	= ( $node->attributes('class') ? $node->attributes('class') . ' text_area' : 'text_area' );
		
		$control 				= $control_name.'['.$name.']';
		
		$html = '';
		
		$attributes['value'] 	= $value;
		$attributes['type'] 	= 'text';
		$attributes['name'] 	= $control;
		$attributes['id'] 		= preg_replace('#[^a-z0-9_-]#i', '', $control_name.$name);
		
		$filter					= isset($attributes['data-filter']) ? $attributes['data-filter'] : '';
		
		$html .= '<input';
		
		foreach ($attributes as $k => $v) {
			if (!in_array($k, array('default', 'label', 'description'))) {
				$html .= ' ' . $k . ' = "' . $v . '"';
			}	
		}

		$html .= ' />';
		
		$options = array(
			'width' 	=> 765,
			'height'	=> 480,
			'modal' 	=> true,
			'id'		=> $attributes['id'] . '_browser'
		);

		$model = JModel::getInstance('WFModel');
		
		$html .= '<a href="' . $model->getBrowserLink($attributes['id'], $filter) . '" class="dialog browser" target="_blank" data-options=\'' . json_encode($options) . '\' title="' . WFText::_( 'WF_BROWSER_TITLE' ) . '"><span class="browser"></span></a>';
		
		return $html;
	}
}
?>