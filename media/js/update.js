/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2015 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce.Update = {
        updates: {},
        options: {
            language: {
                'check': 'Check for Updates',
                'install': 'Install Updates',
                'installed': 'Installed',
                'no_updates': 'No Updates Available',
                'high': 'High',
                'medium': 'Medium',
                'low': 'Low',
                'full': 'Full Install',
                'patch': 'Patch',
                'auth_failed': 'Authorisation Failed',
                'install_failed': 'Install Failed',
                'update_info': 'Update Information',
                'install_info': 'Install Information',
                'check_updates': 'Checking for Updates...',
                'info': 'Show Information',
                'read_more': 'More',
                'read_less': 'Less'
            }
        },
        /**
         * Initialise Updates
         * @param {Object} options
         */
        init: function(options) {
            var self = this;

            $.extend(this.options, options);

            $('button#update-button').click(function() {
                self.execute(this);
            }).click();
        },
        execute: function(el) {
            if ($(el).hasClass('check')) {
                this.check(el);
            }

            if ($(el).hasClass('install')) {
                this.download(el);
            }
        },
        translate: function(s, v) {
            return this.options.language[s] || v;
        },
        /**
         * Check for updates and create list of available updates
         * @param {Object} btn Button element
         */
        check: function(btn) {
            var self = this;

            $('#install-button').remove();

            var list = $('div#updates-list');

            $('div.body', list).html('<div class="item">' + this.translate('check_updates') + '</div>');

            $(btn).addClass('loading').prop('disabled', true);

            // Array of priority values
            var priority = ['<span class="label label-important priority">' + this.translate('high') + '</span>', '<span class="label label-warning priority">' + this.translate('medium') + '</span>', '<span class="label label-info priority">' + this.translate('low') + '</span>'];

            $.getJSON("index.php?option=com_jce&view=updates&task=update&step=check", {}, function(r) {
                $(btn).removeClass('loading');
                $(btn).prop('disabled', false);

                $('div.body', list).empty();

                if (r) {
                    if ($.type(r) == 'string') {
                        r = $.parseJSON(r);
                    }

                    if (r.error) {
                        $('div.body', list).html('<div class="item error">' + r.error + '</div>');
                        return false;
                    }

                    if (r.length) {
                        // clone check button as install but set disabled until items checked
                        $(btn).clone().click(function() {
                            self.execute(this);
                        }).insertAfter(btn).attr({
                            'id': 'install-button',
                            'disabled': 'disabled'
                        }).removeClass('check').addClass('install').prop('disabled', true).html('<i class="icon-arrow-up"></i>&nbsp;' + self.translate('install'));

                        $.each(r, function(n, s) {
                            // authorisation success or not required
                            $('div.body', list).append('<div class="item"><div class="span1"><span class="checkbox" data-uid="' + s.id + '"></span></div><div class="span5">' + s.title + '</div><div class="span3">' + s.version + '</div><div class="span3">' + priority[s.priority - 1] + '</div></div>');

                            // info...
                            var $info = $('<div class="item info">' + s.text + '</div>').appendTo($('div.body', list));

                            var $readmore = $('<span class="readmore">' + self.translate('read_more', 'More') + '</span>').click(function() {
                                // hide others
                                $('div.body .item', list).toggle();

                                $(this).toggleClass('readmore readless').parent().toggleClass('expand').toggle().prev('.item').toggle();

                                $(this).html(function() {
                                    if ($(this).hasClass('readless')) {
                                        return self.translate('read_less', 'Less');
                                    } else {
                                        return self.translate('read_more', 'More');
                                    }
                                });

                            }).appendTo($info);

                            if (!$.support.leadingWhitespace) {
                                $readmore.css('top', 0);
                            }

                            // scroll readmore
                            $('div.body div.item.info', list).on('scroll', function() {
                                if (!$.support.leadingWhitespace) {
                                    $readmore.css('top', $(this).scrollTop());
                                } else {
                                    $readmore.css('bottom', -$(this).scrollTop());
                                }
                            });

                            // fix width if scrollbar showing
                            var sb = $('div.body', list).get(0).clientWidth - $('div.header', list).get(0).clientWidth;

                            if (sb < 0) {
                                $('div.body', list).addClass('scrolling');
                            }

                            /*if ($('div.header', list).innerWidth() > $('div.body', list).innerWidth()) {
                                $('div.body div.item', list).css('margin-right', -10);
                            }*/

                            var el = $('span[data-uid=' + s.id + ']');

                            if (s.auth) {
                                // check checkbox if forced update or high priority
                                if (parseInt(s.forced) == 1 || s.priority == 1) {
                                    $(el).addClass('checked').addClass('disabled');
                                    $('button#install-button').prop('disabled', false);

                                    // disable any updates that this particular update overrides / negates eg: an equivalent patch or full version
                                    if (s.negates) {
                                        $('span[data-uid=' + s.negates + ']').removeClass('checked').addClass('disabled');
                                    }
                                }
                                // disable checkbox if forced update
                                if (parseInt(s.forced) == 1) {
                                    $(el).addClass('disabled');
                                }
                                // check required checkbox
                                if (s.required) {
                                    $('span[data-uid=' + s.required + ']').addClass('checked');
                                }

                                // checkbox events
                                $(el).click(function() {
                                    if ($(this).is('.disabled, .error, .alert')) {
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
                                            $('span[data-uid=' + s.negates + ']').removeClass('checked').addClass('disabled');
                                        } else {
                                            $('span[data-uid=' + s.negates + ']').removeClass('disabled');
                                        }
                                    }
                                    
                                    var len = $('div.body span.checkbox.checked', list).length;

                                    if (len) {
                                        $('button#install-button').attr('disabled', '').prop('disabled', false);
                                        
                                        if (len == $('div.body span.checkbox', list).length) {
                                        	$('div.header div:first-child span.checkbox', list).addClass('checked');
                                        } else {
                                        	$('div.header div:first-child span.checkbox', list).removeClass('checked');
                                        }
                                                                                
                                    } else {
                                        $('button#install-button').attr('disabled', 'disabled').prop('disabled', true);
                                        $('div.header div:first-child span.checkbox', list).removeClass('checked');
                                    }
                                });

                            } else {
                                $(el).removeClass('disabled').addClass('alert');
                                $('div.body', list).append('<div class="item error">' + s.title + ' : ' + self.translate('auth_failed') + '</div>');
                            }
                        });
                        
                        if (r.length > 1) {
                            $('<span class="checkbox"></span>').appendTo($('div.header div:first', list)).click(function() {
                                $('div.body span.checkbox', list).click();
                            });
                        }
                        
                    } else {
                        $('div.body', list).append('<div class="item">' + self.translate('no_updates') + '</div>');
                    }
                } else {
                    $('div.body', list).append('<div class="item">' + self.translate('no_updates') + '</div>');
                }
            });

        },
        /**
         * Download selected updates
         * @param {Object} btn Button element
         */
        download: function(btn) {
            var t = this, n = 1;

            // get all checked updates
            var s = $('#updates-list div.body div.item span.checkbox.checked');
            // disable all while downloading
            $(s).addClass('disabled');
            // disable button while downloading
            $(btn).prop('disabled', true);

            $('button#update-button').prop('disabled', true);

            $.extend(t.updates, {
                'joomla': [],
                'jce': []
            });

            $.each(s, function() {
                var el = this, uid = $(this).data('uid');

                $(el).removeClass('error').addClass('loader');
                $.post("index.php?option=com_jce&view=updates&task=update&step=download", {
                    'id': uid
                }, function(r) {
                    if (r && r.error) {
                        // add error icon and message
                        $(el).removeClass('loader disabled check').addClass('error').parents('div.item').next('div.item.info').replaceWith('<div class="item error">' + r.error + '</div>');
                    } else {
                        // download success
                        if (r.file) {
                            $(el).addClass('downloaded');
                            // set id
                            $.extend(r, {
                                'id': uid
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
        install: function(btn) {
            var t = this, n = 0;
            var s = $('div.body div.item span.checkbox.checked.downloaded');

            /**
             * Run install on each update
             * @param {Int} n index
             */
            function __run() {
                // select joomla or jce updates
                var updates = t.updates['joomla'].length ? t.updates['joomla'] : t.updates['jce'];

                // any left?
                if (updates.length) {
                    var file = updates[0], id = file.id, el = $('span[data-uid=' + id + ']');

                    // double check to see it is a downloaded file
                    if ($(el).hasClass('downloaded')) {
                        $.post("index.php?option=com_jce&view=updates&task=update&step=install", file, function(r) {
                            $(el).removeClass('loader');

                            if (r && r.error) {
                                $(el).removeClass('loader disabled check').addClass('error').parents('div.item').next('div.item.info').replaceWith('<div class="item error">' + r.error + '</div>');
                            } else {
                                // install success
                                $(el).addClass('tick').removeClass('check');
                                $('div#update_info_' + id, '').append('<h3>' + t.options.language['install_info'] + '</h3><div>' + r.text + '</div>');

                                $(el).parents('div.item').find('span.priority').removeClass('label-warning label-important label-info').addClass('label-success').html(t.options.language['installed']);
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

                                $('button#update-button').prop('disabled', false);

                                // close
                                window.setTimeout(function() {
                                    window.parent.document.location.href = "index.php?option=com_jce&view=cpanel";
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
                $('button#update-button').prop('disabled', false);
            }
        }

    };
})(jQuery);