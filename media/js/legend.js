/**
 * @version		$Id: profiles.js 203 2011-06-01 19:02:19Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    // Create Profiles object
    $.jce.Legend = {
    	init : function() {
    		var p = $('span.profileLayoutContainer', parent.window.document.getElementById('profileLayoutTable'));
    		
    		$('tr', '#jce').hover(function() {
    			$('span.mce_' + $(this).attr('title'), p).addClass('focus');
    		}, function() {
    			$('span.mce_' + $(this).attr('title'), p).removeClass('focus');
    		});
    	}
	};
})(jQuery);