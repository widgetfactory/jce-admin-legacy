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
    Joomla.submitbutton = submitbutton = function(button) {		
        try {
            Joomla.submitform(button);
        } catch(e) {
            submitform(button);
        }
    };
    
    $.jce.Installer = {
        init : function(options) {
            
            $(":file").upload(options);
            
            if ($('body').hasClass('ui-jquery')) {               
                $('button#upload_button').button({
                    icons : {
                        primary : 'icon-install'
                    }
                });
                
                $('#upload_button_container button').button({
                    icons : {
                        primary : 'icon-browse'
                    }
                });
            }
            
            var n = $('#tabs-plugins, #tabs-extensions, #tabs-languages, #tabs-related').find('input[type="checkbox"]');
            
            $(n).click(function() {               
                $('input[name="boxchecked"]').val($(n).filter(':checked').length); 
            });

            $('#upload_button').click( function(e) {
                //if ($('div#tabs input:checkbox:checked').length) {
                $(this).addClass('loading');
                $('input[name="task"]').val('install');
                $('form[name="adminForm"]').submit();
                //}
                e.preventDefault();
            });

            $('button.install_uninstall').click( function(e) {
                if ($('div#tabs input:checkbox:checked').length) {
                    $(this).addClass('ui-state-loading');
                    $('input[name="task"]').val('remove');
                    $('form[name="adminForm"]').submit();
                }
                e.preventDefault();
            });
        }
    };
})(jQuery);