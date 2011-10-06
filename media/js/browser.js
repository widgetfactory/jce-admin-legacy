/**
 * @version   $Id: browser.js 201 2011-05-08 16:27:15Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

(function($) {        
    $.WFBrowserWidget = {
        options : {
    		element : null,
    		
    		plugin : {
    			plugin	: 'browser',
    			root	: '',
    			site	: '',
    			help	: function() {
	    			window.parent.$jce.createDialog({
	    				src		: 'index.php?option=com_jce&view=help&tmpl=component&section=editor&category=browser',
	    				type	: 'help',
	    				options	: {
	    					width 	: 780,
	    					height	: 560,
	    				}
	    			});
    			}
    		},
    		manager : {
    			upload : {
    				insert : false
    			},
    			expandable : false
    		},
    		close : null
    	},
    		
    	init : function(options) {    		
    		var self = this, win = window.parent, doc = win.document;
    		
    		$.extend(true, this.options, options);

    		$('<input type="hidden" id="src" value="" />').appendTo(document.body);
    		
    		$.Plugin.init(this.options.plugin);
    		
    		$('button#insert, button#cancel').hide();
    		
    		if (this.options.element) {
    			// add insert button action
	            $('button#insert').show().click( function(e) {
	                self.insert();
	                self.close();
	                e.preventDefault();
	            });
	            
	            $('button#cancel').show().click( function(e) {
	            	self.close();
	            	
	            	e.preventDefault();
	            });
	            
	            var src = doc.getElementById(this.options.element).value || '';
	            
	            $('#src').val(src);
    		}

    		// Create File Browser
            WFFileBrowser.init($('#src'), $.extend(this.options.manager, {}));
       },
       
       insert : function() {
       		if (this.options.element) {
       			var src = WFFileBrowser.getSelectedItems(0);

       			window.parent.document.getElementById(this.options.element).value = $(src).data('url');
       		}
       },
       
       close : function() {
       		var fn = this.options.close;
       		if (fn)	{
       			fn.call(this);
       		} else {
       			window.parent.$jce.closeDialog('#' + this.options.element + '_browser');
       		}
       }
    }
})(jQuery);

//fake tinyMCE object for language files
var tinyMCE = {
	addI18n : function(p, o) {	
		return $.Plugin.addI18n(p, o);
	}
};