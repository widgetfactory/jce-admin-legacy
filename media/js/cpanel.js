/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2013 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce.CPanel = {
        init : function(options) {
            if (options.feed) {
                $('ul.newsfeed').addClass('loading').html('<li>' + options.labels.feed + '</li>');

                // Get feed
                $.getJSON("index.php?option=com_jce&view=cpanel&task=feed", {}, function(r) {
                    $('ul.newsfeed').removeClass('loading').empty();

                    $.each(r.feeds, function(k, n) {
                        $('ul.newsfeed').append('<li><a href="' + n.link + '" target="_blank" title="' + n.title + '">' + n.title + '</a></li>');
                    });

                });

            }

            if (options.updates) {
                // Check updates
                $.getJSON("index.php?option=com_jce&view=updates&task=update&step=check", {}, function(r) {                    
                    if (r) {                        
                        if ($.type(r) == 'string') {
                            r = $.parseJSON(r);
                        }
                        
                        if (r.error) {
                            var $list = $('div#jce ul.adminformlist').append('<li><span>' + options.labels.updates + '</span><span class="updates error">' + r.error + '</span></li>');
                            return false;
                        }
                        
                        if (r.length) {
                            var $list = $('div#jce ul.adminformlist').append('<li><span>' + options.labels.updates + '</span><span class="updates"><a title="' + options.labels.updates + '" class="updates" href="#">' + options.labels.updates_available + '</a></span></li>');
                        
                            $('a.updates', $list).click( function(e) {
                                // trigger Joomla! 3.0 button
                                $('#toolbar-updates button').click();
                            
                                // trigger toolbar button
                                $('#toolbar-updates a.modal').each( function() {
                                    $.jce.createDialog(this, {
                                        src 	: $(this).attr('href'),
                                        options : {
                                            'width'   : 780,
                                            'height'  : 560
                                        }
                                    });
                                    e.preventDefault();
                                });
                            });
                        }
                    }
                });

            }
            // Open config/preferences dialog
            $('#newsfeed_enable').click(function(e) {
                // trigger Joomla! 3.0 button
                $('#toolbar-options button').click(); 
                
                // trigger toolbar button
                $('#toolbar-popup-options a.modal, #toolbar-config a.modal').each(function() {
                    $.jce.createDialog(this, {
                        src 	: $(this).attr('href'),
                        options : {
                            'width'   : 780,
                            'height'  : 560
                        }
                    });
                });
                
                e.preventDefault();
            });
        }
    };
})(jQuery);