/**
 * @version   $Id: preferences.js 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce.Preferences = {
    	init : function() {
    		// Tabs
           	$('#tabs').tabs();
           	
           	$('#access-accordian').accordion({ collapsible: true });
    	},
    	
    	close : function() {
    		this.init();

    		window.setTimeout(function(){
    			window.parent.document.location.href="index.php?option=com_jce&view=cpanel"}, 
    		1000);
    	}
	};
})(jQuery);
