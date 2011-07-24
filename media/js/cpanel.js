/**
 * @version   $Id: cpanel.js 203 2011-06-01 19:02:19Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
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
                        $('ul.newsfeed').append('<li><a href="' + n.link + '" title="' + n.title + '">' + n.title + '</a></li>');
                    });

                });

            }

            if (options.updates) {
                // Check updates
                $.getJSON("index.php?option=com_jce&view=updates&task=update&step=check", {}, function(r) {
                    if (r && r.length) {
                        $('div#jce ul.adminformlist').append('<li><span>' + options.labels.updates + '</span><span class="updates"><a title="' + options.labels.updates + '" class="dialog updates" href="index.php?option=com_jce&amp;view=updates&amp;tmpl=component">' + options.labels.updates_available + '</a></span></li>');

                        $('a.dialog.updates', 'div#jce ul.adminformlist').click( function(e) {
                            $.jce.createDialog({
                            	width	: 760,
                            	height	: 540,
                                src 	: $(this).attr('href'),
                                options : $(this).data('options'),
                                modal	: true,
                                type	: 'updates',
                                title	: $(this).attr('title')
                            });
                            e.preventDefault();
                        });

                    }
                });

            }

        }
    };
})(jQuery);