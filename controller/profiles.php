<?php
/**
 * @version		$Id: profiles.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright 	Copyright © 2005 - 2007 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class WFControllerProfiles extends WFController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct();

		$this->registerTask('apply', 		'save');
		$this->registerTask('unpublish', 	'publish');
		$this->registerTask('enable', 		'publish');
		$this->registerTask('disable', 		'publish');
		$this->registerTask('orderup', 		'order');
		$this->registerTask('orderdown', 	'order');
	}

	function display()
	{
		parent::display();
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'RESTRICTED' );

		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();
		$cid     = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		if (count( $cid ) < 1) {
			JError::raiseError(500, WFText::_('WF_PROFILES_SELECT_ERROR') );
		}

		$cids = implode( ',', $cid );

		$query = 'DELETE FROM #__wf_profiles'
		. ' WHERE id IN ( '.$cids.' )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}

		$msg = JText::sprintf('WF_PROFILES_DELETED', count( $cid ));
		$this->setRedirect( 'index.php?option=com_jce&view=profiles', $msg );
	}

	function copy()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'RESTRICTED' );

		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();
		$cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$n		= count( $cid );
		if ($n == 0) {
			return JError::raiseWarning( 500, WFText::_('WF_PROFILES_SELECT_ERROR'));
		}

		$row 	= JTable::getInstance('profiles', 'WFTable');

		foreach ($cid as $id){
			// load the row from the db table
			$row->load( (int) $id );
			$row->name 			   = JText::sprintf('WF_PROFILES_COPY_OF', $row->name );
			$row->id 			     = 0;
			$row->published 	 = 0;

			if (!$row->check()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
			if (!$row->store()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
			$row->checkin();
			$row->reorder( 'ordering='.$db->Quote( $row->ordering ) );
		}
		$msg = JText::sprintf('WF_PROFILES_COPIED', $n);
		$this->setRedirect( 'index.php?option=com_jce&view=profiles', $msg );
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'RESTRICTED' );

		$db   	= JFactory::getDBO();
		$row 	= JTable::getInstance('profiles', 'WFTable');
		$task 	= $this->getTask();

		// get components
		$components = JRequest::getVar( 'components', array(), 'post', 'array' );
		// get usertypes
		$types 		= JRequest::getVar( 'types', array(), 'post', 'array' );
		// get users
		$users 		= JRequest::getVar( 'users', array(), 'post', 'array' );

		if (!$row->bind(JRequest::get('post'))) {
			JError::raiseError(500, $row->getError() );
		}
		
		$row->types 		= implode(',', $types);
		$row->components 	= implode(',', $components);
		$row->users 		= implode(',', $users);

		$data 				= new StdClass();
		// get params array
		$params = JRequest::getVar('params', array(), 'POST', 'array');
		
		if (isset($params['editor'])) {
			$data->editor = WFParameterHelper::toObject($params['editor']);
		}
		$plugins = explode(',', $row->plugins);

		foreach ($plugins as $plugin) {
			// add plugin params to array
			if (isset($params[$plugin])) {
				$data->$plugin = WFParameterHelper::toObject($params[$plugin]);
			}
		}
		$row->params = json_encode($data);

		if (!$row->check()) {
			JError::raiseError(500, $row->getError());
		}
		if (!$row->store()) {
			JError::raiseError(500, $row->getError());
		}
		$row->checkin();

		switch ( $task )
		{
			case 'apply':
				$msg = JText::sprintf('WF_PROFILES_SAVED_CHANGES', $row->name );
				$this->setRedirect( 'index.php?option=com_jce&view=profiles&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = JText::sprintf('WF_PROFILES_SAVED', $row->name );
				$this->setRedirect( 'index.php?option=com_jce&view=profiles', $msg );
				break;
		}
	}

	/**
	 * Generic publish method
	 * @return
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or die ('Invalid Token');

		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$cid 	= JRequest::getVar('cid', array (0), 'post', 'array');

		JArrayHelper::toInteger($cid, array (0));

		switch($this->getTask()) {
			case 'publish':
			case 'enable':
				$publish = 1;
				break;
			case 'unpublish':
			case 'disable':
				$publish = 0;
				break;
		}

		$view 	= JRequest::getCmd('view');

		if (count($cid) < 1) {
			$action = $publish ? WFText::_('WF_LABEL_PUBLISH') : WFText::_('WF_LABEL_UNPUBLISH');
			JError::raiseError(500, JText::sprintf('WF_PROFILES_VIEW_SELECT', $view, $action));
		}

		$cids = implode(',', $cid);

		$query = 'UPDATE #__wf_profiles SET published = '.(int)$publish
		.' WHERE id IN ( '.$cids.' )'
		.' AND ( checked_out = 0 OR ( checked_out = '.(int)$user->get('id').' ))'
		;
		$db->setQuery($query);

		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg());
		}

		if (count($cid) == 1) {
			$row = JTable::getInstance('profiles', 'WFTable');
			$row->checkin($cid[0]);
		}
		$this->setRedirect('index.php?option=com_jce&view=profiles');
	}

	function order()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$db = JFactory::getDBO();

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$uid    = $cid[0];
		$inc    = ( $this->getTask() == 'orderup' ? -1 : 1 );

		$row = JTable::getInstance('profiles', 'WFTable');
		$row->load( $uid );
		$row->move( $inc );

		$this->setRedirect( 'index.php?option=com_jce&view=profiles' );
	}

	function saveorder( )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'RESTRICTED' );

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$db			= JFactory::getDBO();
		$total		= count( $cid );
		$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order, array(0));

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$row 		= JTable::getInstance('profiles', 'WFTable');
		$conditions = array();

		// update ordering values
		for ( $i=0; $i < $total; $i++ )
		{
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError(500, $db->getErrorMsg() );
				}
				// remember to updateOrder this group
				$condition = ' AND ordering > -10000 AND ordering < 10000';
				$found = false;
				foreach ( $conditions as $cond )
				{
					if ($cond[1]==$condition) {
						$found = true;
						break;
					}
				}
				if (!$found) $conditions[] = array($row->id, $condition);
			}
		}

		// execute updateOrder for each group
		foreach ( $conditions as $cond ) {
			$row->load( $cond[0] );
			$row->reorder( $cond[1] );
		}

		$msg = WFText::_( 'WF_PROFILES_ORDERING_SAVED' );
		$this->setRedirect( 'index.php?option=com_jce&view=profiles', $msg );
	}

	function cancelEdit()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('RESTRICTED');

		$view = JRequest::getCmd('view');

		$db =JFactory::getDBO();
		$row =JTable::getInstance($view, 'WFTable');
		$row->bind(JRequest::get('post'));
		$row->checkin();

		$this->setRedirect(JRoute::_('index.php?option=com_jce&view='.$view, false));

	}

	function export()
	{
		$mainframe  = JFactory::getApplication();
		$db 		= JFactory::getDBO();
		$tmp 		= $mainframe->getCfg('tmp_path');

		$buffer  	 = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
		$buffer 	.= "\n".'<export type="profiles">';
		$buffer 	.= "\n\t".'<profiles>';

		$cid     = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		if (count( $cid ) < 1) {
			JError::raiseError(500, WFText::_('WF_PROFILES_SELECT_ERROR'));
		}

		$cids = implode( ',', $cid );

		// get froup data
		$query = 'SELECT * FROM #__wf_profiles'
		. ' WHERE id IN ('.$cids.')'
		;

		$db->setQuery($query);
		$profiles = $db->loadObjectList();

		foreach ($profiles as $profile) {
			// remove some stuff
			unset($profile->id);
			unset($profile->checked_out);
			unset($profile->checked_out_time);
			// set published to 0
			$profile->published = 0;
				
			$buffer .= "\n\t\t";
			$buffer .= '<profile>';
			 
			foreach ($profile as $key => $value) {
				if ($key == 'params') {
					$buffer .= "\n\t\t\t".'<'.$key.'>';
					if ($value) {
						$params = explode("\n", $value);
						foreach ($params as $param) {
							if ($param !== '') {
								$buffer .= "\n\t\t\t\t".'<param>'.$param.'</param>';
							}
						}
						$buffer .= "\n\t\t\t\t";
					}
					$buffer .= '</'.$key.'>';
				} else {
					$buffer .= "\n\t\t\t".'<'.$key.'>'.$this->encodeData($value).'</'.$key.'>';
				}
			}
			$buffer .= "\n\t\t</profile>";
		}
		$buffer .= "\n\t</profiles>";
		$buffer .= "\n</export>";

		// set_time_limit doesn't work in safe mode
		if (!ini_get('safe_mode')) {
			@set_time_limit(0);
		}

		$name = 'jce_profile_'.date('Y_m_d').'.xml';

		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Content-Transfer-Encoding: binary");
		header ("Content-Type: text/xml");
		header('Content-Disposition: attachment;'
		.' filename="'.$name.'";'
		);

		echo $buffer;

		exit();
	}

	/**
	 * Process XML restore file
	 * @param object $xml
	 * @return boolean
	 */
	function import()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('RESTRICTED');
		
		$mainframe 	= JFactory::getApplication();
		$tmp 		= $mainframe->getCfg('tmp_path');
		$file 		= JRequest::getVar('import', '', 'files', 'array');
		$input		= JRequest::getVar('import_input');

		$model 		= $this->getModel('profiles', 'WFModel');
		
		jimport('joomla.filesystem.file');

		if (!is_array($file)) {
			$mainframe->enqueueMessage(WFText::_('WF_PROFILES_UPLOAD_NOFILE'), 'error');
		} else {
			if (!$file['name'] || !$file['tmp_name']) {
				if (JFile::exists($input)) {
					$this->processImport($input);
				} else {
					$mainframe->enqueueMessage(WFText::_('WF_PROFILES_IMPORT_NOFILE'), 'error');
				}
			} else {
				// Check if there was a problem uploading the file.
				if ($file['error'] || $file['size'] < 1) {
					$mainframe->enqueueMessage(WFText::_('WF_PROFILES_UPLOAD_FAILED'), 'error');
				} else {
					$dest = $tmp.DS.$file['name'];
					if (JFile::upload($file['tmp_name'], $dest)) {
						if (JFile::exists($dest)) {
							$model->processImport($dest);
						} else {
							$mainframe->enqueueMessage(WFText::_('WF_PROFILES_UPLOAD_FAILED'), 'error');
						}
					} else {
						$mainframe->enqueueMessage(WFText::_('WF_PROFILES_UPLOAD_FAILED'), 'error');
					}
				}
			}
		}

		$this->setRedirect('index.php?option=com_jce&view=profiles');
	}

	/**
	 * CDATA encode a parameter if it contains & < > characters, eg: <![CDATA[index.php?option=com_content&view=article&id=1]]>
	 * @param object $param
	 * @return CDATA encoded parameter or parameter
	 */
	function encodeData($data)
	{
		if (preg_match('/[<>&]/', $data)) {
			$data = '<![CDATA['.$data.']]>';
		}

		$data = preg_replace('/"/', '\"', $data);

		return $data;
	}
}
?>