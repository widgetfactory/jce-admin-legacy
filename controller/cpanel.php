<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

class WFControllerCpanel extends WFController 
{
    function feed() {
        $model = $this->getModel('cpanel');
        $feeds = $model->getFeeds();

        exit(json_encode(array('feeds' => $feeds)));
    }
}
