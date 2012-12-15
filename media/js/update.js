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
    $.jce.Update = {

        updates : {},

        options : {
            language : {
                'check' 		: 'Check for Updates',
                'install' 		: 'Install Updates',
                'installed'		: 'Installed',
                'no_updates'	: 'No Updates Available',
                'high'			: 'High',
                'medium'		: 'Medium',
                'low'			: 'Low',
                'full'			: 'Full Install',
                'patch'			: 'Patch',
                'auth_failed'	: 'Authorisation Failed',
                'install_failed': 'Install Failed',
                'update_info'	: 'Update Information',
                'install_info'	: 'Install Information',
                'check_updates'	: 'Checking for Updates...'
            }
        },

        /**
         * Initialise Updates
         * @param {Object} options
         */
        init : function(options) {
            var self = this;

            $.extend(this.options, options);

            $('button#update-button').button({
                icons : {
                    primary : 'icon-update'
                }
            }).click( function() {
                self.execute(this);
            }).click();
        },

        execute : function(el) {
            if ($(el).hasClass('check')) {
                this.check(el);
            }

            if ($(el).hasClass('install')) {
                this.download(el);
            }
        },

        /**
         * Check for updates and create list of available updates
         * @param {Object} btn Button element
         */
        check : function(btn) {
            var t = this;

            $('button.download').remove();
            $('button.install').remove();

            var list = $('table#updates-list tbody');
            var info = $('div#updates-info');

            $(list).html('<tr><td colspan="5" style="text-align:center;">' + this.options.language['check_updates'] + '</td></tr>');
            $(info).empty();

            $(btn).addClass('loading').button('disable');

            var priority = {
                1 : '<span class="priority high">' + this.options.language['high'] + '</span>',
                2 : '<span class="priority medium">' + this.options.language['medium'] + '</span>',
                3 : '<span class="priority low">' + this.options.language['low'] + '</span>'
            };

            $.getJSON("index.php?option=com_jce&view=updates&task=update&step=check", {}, function(r) {
                $(btn).removeClass('loading');
                $(btn).button('enable');

                $(list).empty();

                if (r) {
                    if ($.type(r) == 'string') {
                        r = $.parseJSON(r);
                    }
                    
                    if (r.error) {
                        $(list).html('<tr><td colspan="5" style="text-align:center;background-color:#FFCCCC;">'+ r.error +'</td></tr>');
                        return false;
                    }

                    if (r.length) {
                        // clone check button as install but set disabled until items checked
                        $(btn).clone().button({
                            icons : {
                                primary : 'icon-install'
                            },
                            disabled : true,
                            label : t.options.language.install
                        }).click( function() {
                            t.execute(this);
                        }).insertAfter(btn).attr({
                            'id' : 'install-button',
                            'disabled' : 'disabled'
                        }).removeClass('check').addClass('install');

                        $.each(r, function(n, s) {
                            // authorisation success or not required
                            $(list).append('<tr style="cursor:pointer;"><td><span class="checkbox" data-uid="'+ s.id +'"></span></td><td>' + s.title + '</td><td align="center">' + t.options.language[s.type] + '</td><td align="center">' + s.version + '</td><td align="center">'+priority[s.priority]+'</td></tr>');

                            var el = $('span[data-uid='+ s.id +']');

                            if (s.auth) {
                                // check checkbox if forced update or high priority
                                if (parseInt(s.forced) == 1 || s.priority == 1) {
                                    $(el).addClass('checked').addClass('disabled');
                                    $('button#install-button').button('enable');

                                    // disable any updates that this particular update overrides / negates eg: an equivalent patch or full version
                                    if (s.negates) {
                                        $('span[data-uid='+ s.negates +']').removeClass('checked').addClass('disabled');
                                    }
                                }
                                // disable checkbox if forced update
                                if (parseInt(s.forced) == 1) {
                                    $(el).addClass('disabled');
                                }
                                // check required checkbox
                                if (s.required) {
                                    $('span[data-uid='+ s.required +']').addClass('checked');
                                }

                                // checkbox events
                                $(el).click( function() {
                                    if ($(this).hasClass('disabled') || $(this).hasClass('error')) {
                                        return;
                                    }
								
                                    if ($(this).hasClass('checked')) {
                                        $(this).removeClass('checked');
                                    } else {
                                        $(this).addClass('checked');
                                    }

                                    // disable any updates that this particular update overrides / negates eg: an equivalent patch or full version
                                    if (s.negates) {
                                        if ($(this).hasClass('checked')) {
                                            $('span[data-uid='+ s.negates +']').removeClass('checked').addClass('disabled');
                                        } else {
                                            $('span[data-uid='+ s.negates +']').removeClass('disabled');
                                        }
                                    }

                                    if ($('span.checkbox.checked', $(list)).length) {
                                        $('button#install-button').attr('disabled', '').button('enable');
                                    } else {
                                        $('button#install-button').attr('disabled', 'disabled').button('disable');
                                    }
                                });

                            } else {
                                $(el).addClass('disabled').addClass('alert');
                                $(list).append('<tr><td colspan="5" style="text-align:center;background-color:#FFCCCC;">' + s.title + ' : ' + t.options.language['auth_failed'] +'</td></tr>');
                            }

                            $(info).append('<div class="update_info" id="update_info_'+ s.id +'"><h3>' + s.title + '</h3><div>' + s.text + '</div></div>');
                            $('div#update_info_'+ s.id).hide();

                            // show first list item info and select
                            if (n == 0) {
                                $('div#update_info_'+ s.id).fadeIn();
                                $(el).parents('tr').addClass('selected');
                            }

                            // add info click event
                            $(el).parents('tr').click( function() {
                                // remove all selections
                                $('tr.selected', $(list)).removeClass('selected');

                                $(this).addClass('selected');

                                $(info).children('div.update_info').hide();
                                $('div#update_info_'+ s.id).fadeIn();
                            });

                        });

                        $(list).find('tbody tr:odd').addClass('odd'); 
                    } else {
                        $(list).html('<tr><td colspan="5" style="text-align:center;">'+ t.options.language['no_updates'] +'</td></tr>');
                    }
                } else {
                    $(list).html('<tr><td colspan="5" style="text-align:center;">'+ t.options.language['no_updates'] +'</td></tr>');
                }
            });

        },

        /**
         * Download selected updates
         * @param {Object} btn Button element
         */
        download : function(btn) {
            var t = this, n = 1;

            // get all checked updates
            var s = $('table tbody span.checkbox.checked');
            // disable all while downloading
            $(s).addClass('disabled');
            // disable button while downloading
            $(btn).button('disable');

            $('button#update-button').button('disable');

            $.extend(t.updates, {
                'joomla' 	: [],
                'jce'		: []
            });

            $.each(s, function() {
                var el = this, uid = $(this).data('uid');

                $(el).removeClass('error').addClass('loader');
                $.post("index.php?option=com_jce&view=updates&task=update&step=download", {
                    'id' : uid
                }, function(r) {
                    if (r && r.error) {
                        // add error icon and message
                        $(el).removeClass('loader disabled').addClass('error');
                        $('<tr><td colspan="5" style="text-align:center;background-color:#FFCCCC;">'+ r.error +'</td></tr>').insertAfter($(el).parents('tbody tr'));
                    } else {
                        // download success
                        if (r.file) {
                            $(el).addClass('downloaded');
                            // set id
                            $.extend(r, {
                                'id' : uid
                            });
                            // store result
                            t.updates[r.installer].push(r);
                        }
                    }
                    // all downloaded
                    if (n == (s.length)) {
                        // run install
                        t.install(btn);
                    }
                    n++;
                }, 'json');

            });

        },

        /**
         * Install updates
         * @param {Object} btn Button element
         */
        install : function(btn) {
            var t = this, n = 0;
            var s = $('table tbody span.checkbox.checked.downloaded');

            /**
             * Run install on each update
             * @param {Int} n index
             */
            function __run() {
                // select joomla or jce updates
                var updates = t.updates['joomla'].length ? t.updates['joomla'] : t.updates['jce'];

                // any left?
                if (updates.length) {
                    var file = updates[0], id = file.id, el = $('span[data-uid='+ id +']');

                    // double check to see it is a downloaded file
                    if ($(el).hasClass('downloaded')) {
                        $.post("index.php?option=com_jce&view=updates&task=update&step=install", file, function(r) {
                            $(el).removeClass('loader');

                            if (r && r.error) {
                                $(el).addClass('error').removeClass('check');
                                $('<tr><td colspan="5" style="text-align:center;background-color:#FFCCCC;">'+ r.error +'</td></tr>').insertAfter($(el).parents('tr'));
                            } else {
                                // install success
                                $(el).addClass('tick').removeClass('check');
                                $('div#update_info_' + id, '').append('<h3>' + t.options.language['install_info'] + '</h3><div>' + r.text + '</div>');

                                $(el).parents('tr').find('span.priority').removeClass('high medium low').addClass('installed').html(t.options.language['installed']);
                            }
                            // remove update
                            updates.splice(0, 1);
                            n++;

                            // run next install
                            if (n < s.length) {
                                __run();
                            } else {
                                // clear updates
                                t.updates = {};

                                $('button#update-button').button('enable');
                                
                                // close
                                window.setTimeout(function(){
                                    window.parent.document.location.href="index.php?option=com_jce&view=cpanel";
                                }, 1000);
                            }

                        }, 'json');

                    }
                }
            }

            // run install queue if at least one checked item
            if (s.length) {
                __run(n);
            } else {
                // enable button
                $('button#update-button').button('enable');
            }
        }

    };
})(jQuery);