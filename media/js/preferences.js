/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2012 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce.Preferences = {
    	init : function() {
    		// Tabs
           	$('#tabs').tabs();
           	
           	$('#access-accordian').accordion({collapsible: true, heightStyle: "content"});
           	
           	$('.hasTip').removeClass('hasTip');
    	},
    	
    	close : function() {
    		this.init();

    		window.setTimeout(function(){
    			window.parent.document.location.href="index.php?option=com_jce&view=cpanel";
    		}, 1000);
    	}
	};
})(jQuery);
