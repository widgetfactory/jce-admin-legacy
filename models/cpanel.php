<?php
/**
 * @version   $Id: cpanel.php 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// load base model
require_once(dirname(__FILE__) . DS . 'model.php');

class WFModelCpanel extends WFModel
{
    function iconButton($link, $image, $text, $description = '', $disabled = false)
    {
        $lang = JFactory::getLanguage();
        
        if ($disabled) {
            $link = '#';
        }
        
        $description = $description ? $text . '::' . $description : $text;
        ?>
        <li class="cpanel-icon hasTip ui-corner-all" title="<?php echo $description;?>">
          <a href="<?php echo $link;?>"><?php echo JHTML::_('image.site', $image, '/components/com_jce/media/img/cpanel/', NULL, NULL, $text);?><?php echo $text;?></a>
        </li>
        <?php
    }
    
    function getVersion()
    {
        // Get Component xml
        $xml = JApplicationHelper::parseXMLInstallFile(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce' . DS . 'jce.xml');
        
        return $xml['version'];
    }
    
    function getLicense()
    {
        $file    	= JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jce' . DS . 'jce.xml';
        $license 	= 'GNU / GPL 2 or later';
        
        $xml = WFXMLElement::getXML($file);
        
        if ($xml) {
            $licence = $xml->licence->data();
        }
        
        return $license;
    }
    
    function getFeeds()
    {
        $app    = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_jce');
        $limit  = $params->get('feed_limit', 2);
        
        $feeds   = array();
        $options = array(
            'rssUrl' => 'http://www.joomlacontenteditor.net/news/feed/rss/latest-news?format=feed',
            'cache_time' => $params->get('feed_cachetime', 86400)
        );
        
        // use this directly instead of JFactory::getXMLParserto avoid the feed data error
        jimport('simplepie.simplepie');
        
        if (!is_writable(JPATH_BASE . DS . 'cache')) {
            $options['cache_time'] = 0;
        }
        $rss = new SimplePie($options['rssUrl'], JPATH_BASE . DS . 'cache', isset($options['cache_time']) ? $options['cache_time'] : 0);
        $rss->force_feed(true);
        $rss->handle_content_type();
        
        if ($rss->init()) {
            $count = $rss->get_item_quantity();
            
            if ($count) {
                $count = ($count > $limit) ? $limit : $count;
                for ($i = 0; $i < $count; $i++) {
                    $feed = new StdClass();
                    $item = $rss->get_item($i);
                    
                    $feed->link        = $item->get_link();
                    $feed->title       = $item->get_title();
                    $feed->description = $item->get_description();
                    
                    $feeds[] = $feed;
                }
            }
        }
        
        return $feeds;
    }
}
?>