<?php
/**
 * @version		$Id: profiles.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Plugin table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class WFTableProfiles extends JTable
{
	/**
	 * Primary Key
	 *
	 *  @var int
	 */
	var $id = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $name = null;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $description = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $components = null;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $area = 0;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $users = null;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $types = null;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $rows = null;
	
	/**
	 *
	 *
	 * @var varchar
	 */
	var $plugins = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $published = 0;
	
	/**
	 *
	 *
	 * @var tinyint
	 */
	var $ordering = 1;

	/**
	 *
	 *
	 * @var int unsigned
	 */
	var $checked_out = 0;

	/**
	 *
	 *
	 * @var datetime
	 */
	var $checked_out_time = 0;

	/**
	 *
	 *
	 * @var text
	 */
	var $params = null;

	function __construct(& $db) {
		parent::__construct('#__wf_profiles', 'id', $db);
	}
}
?>