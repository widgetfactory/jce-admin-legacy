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
	$.jce.Parameter = {
		add : function(element, classname) {
			$(document).ready(function($) {
				var div = 'div.' + classname;		
				
				$(div, $(element).parent()).hide();//.find(':input').attr('disabled', 'disabled');				
				$(div + '[data-type="'+ $(element).val() +'"]', $(element).parent()).show().find(':input').removeAttr('disabled');
				
				$(element).change(function() {
					// hide filesystem parameter containers
					$(div, $(this).parent()).hide();//.find(':input').attr('disabled', 'disabled');
					$(div + '[data-type="'+ $(this).val() +'"]', $(this).parent()).show().find(':input').removeAttr('disabled');
				});
				
			});
		}
	};
})(jQuery);