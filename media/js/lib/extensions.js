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
    $.widget("ui.extensionmapper", {
        options : {
            labels : {
                'type_new' : 'Add new type...',
                'group_new': 'Add new group...'
            },
            defaults : ''
        },

        _init : function() {
            var self = this, el = this.element, v = $(el).val() || '', name = $(el).attr('name').replace(/\[\]/, '');

            // store defaults
            this.defaultMap = {};
            var dv = this.options.defaults || $(el).data('default');
			
            if (dv) {
                $.each(dv.split(';'), function(i, s) {
                    var parts = s.split('=');
					
                    self.defaultMap[parts[0]] = parts[1].split(',');
                });
            }

            // process value
            v = $.type(v) == 'array' ? v.join(';') : v;

            var $input = $('<input type="hidden" name="' + name + '" id="' + $(el).attr('id') + '" />').addClass( function() {
                return $(el).hasClass('create') ? 'create' : '';
            }).insertBefore(el).hide().val(v);

            // remove original element
            $(el).remove();

            // transfer to input
            this.element = $input;

            // create display clone
            $('<input type="text" disabled="disabled" role="presentation" aria-disabled="true" />').val(v).insertBefore(this.element);

            // create edit icon link
            $('<span class="extension_edit"></span>').click( function() {
                var $edit = this;

                if (!this.mapper) {
                    // add loading class
                    $(this).addClass('loader');

                    // build the mapper
                    this.mapper = self._buildMapper();

                    $(this.mapper).hide().insertAfter(this).slideDown(450, function() {
                        // remove loader
                        $($edit).removeClass('loader');
                        // Show scroll buttons
                        $('div.extension_group_container ul.extension_list', this).each( function() {
                            if (this.firstChild.offsetHeight * this.childNodes.length > this.parentNode.offsetHeight) {
                                $(this).parent('div.extension_list_container').next('div.extension_list_scroll_bottom').css('visibility', 'visible');
                            }
                        });

                    });

                } else {
                    $(this.mapper).slideToggle(450);
                }
            }).insertAfter(this.element);

        },

        _buildMapper: function() {
            var self = this, v = $(this.element).val();

            // Create container and add groups
            var $container = $('<div class="extension_mapper" id="' + $(this.element).attr('id') + '_mapper" role="presentation"></div>');

            $.each(v.split(';'), function(i, s) {
                $container.append(self._createGroup(s.split('=')));
            });

            if ($(this.element).hasClass('create')) {
                $('<div class="extension_group_add"><span>' + this.options.labels.group_new + '</span></div>').click( function() {
                    var group = self._createGroup();
                    $(group).hide().insertBefore(this).fadeIn('fast');

                    self._createSortable($('ul.extension_list', group));

                }).appendTo($container);

            }

            this._createSortable($('ul.extension_list', $container));
            
            $container.sortable({
                tolerance	: 'intersect',
                placeholder	: 'sortable-highlight',
                handle		: 'span.extension_group_handle',
                update: function(event, ui) {
                    self._setValues();
                },
                start : function(event, ui) {
                    $(ui.placeholder).width($(ui.item).width()).height($(ui.item).height());
                }
            });

            return $container;
        },

        _createSortable : function(list) {
            var self = this;
            // Create sortable lists
            $(list).sortable({
                connectWith	: 'ul.extension_list',
                placeholder	: 'sortable-highlight',
                update: function(event, ui) {
                    if (!ui.sender)
                        return;

                    self._showScroll($(ui.item).parent(), ['bottom']);
                    self._showScroll($(ui.sender), ['top', 'bottom']);

                    self._setValues();
                }

            });
        },

        /**
         * Create a single list group
         * @param {Array} values
         * @return {Object} Group Object
         */
        _createGroup: function(values) {
            var self = this;

            // set default values as custom
            values = values || ['custom', 'custom'];

            var $tmpl = $('<div class="extension_group_container" role="group">' +
                '	<div class="extension_group_titlebar">'+
                '		<span class="extension_group_handle icon-move"></span>' +
                '		<span class="extension_group_title"></span>' +
                '	</div>' +
                '	<div class="extension_list_add"><span role="button">' + this.options.labels.type_new + '</span></div>' +
                '	<div class="extension_list_scroll_top" role="button"><span class="extension_list_scroll_top_icon"></span></div>'+
                '	<div class="extension_list_container">'+
                '		<ul class="extension_list"></ul>'+
                '	</div>'+
                '	<div class="extension_list_scroll_bottom" role="button"><span class="extension_list_scroll_bottom_icon"></span></div>'+
                '</div>');

            // get group name
            var name = values[0], list = values[1] || '';

            // Create input element if custom
            if (name == 'custom') {
                $('<input type="text" size="8" value="" pattern="[a-zA-Z0-9_-]+" />').change( function() {
                    if (this.value == '')
                        return;

                    var v = this.value.toLowerCase();
                    $('span.extension_group_title', $tmpl).addClass(v).attr('title', v);
                }).appendTo($('span.extension_group_title', $tmpl)).focus().pattern();

                // replace checkbox with remove button
                var $remove = $('<span class="extension_group_remove" role="button"></span>').click( function() {
                    $($tmpl).fadeOut('fast', function() {
                        $tmpl.remove();
                        // Trigger serialization
                        self._setValues();
                    });
                });

                $('div.extension_group_titlebar', $tmpl).append($remove);

            // Set title html
            } else {
                // remove non-word characters
                var key = name.replace(/[\W]/g, '');

                if (this.defaultMap[key]) {
                    // Add checkbox
                    var $check = $('<span class="checkbox" role="checkbox"></span>').addClass( function() {
                        return name.charAt(0) == '-' ? '' : 'checked';
                    }).attr('aria-checked', !(name.charAt(0) == '-'));
	
                    $check.click( function() {
                        var s = name;
	                	
                        if (s.charAt(0) === '-') {
                            s = s.substr(1);
                        }
	                	
                        if ($(this).is('.checked')) {
                            $(this).removeClass('checked').attr('aria-checked', false).prev('span.extension_group_title').attr('title', '-' + s);
                        } else {
                            $(this).addClass('checked').attr('aria-checked', true).prev('span.extension_group_title').attr('title', s);
                        }
                        // Trigger serialization
                        self._setValues();
                    });
	                
                    $('div.extension_group_titlebar', $tmpl).append($check);
                } else {
                    // replace checkbox with remove button
                    var $remove = $('<span class="extension_group_remove" role="button"></span>').click( function() {
                        $($tmpl).fadeOut('fast', function() {
                            $tmpl.remove();
                            // Trigger serialization
                            self._setValues();
                        });
	
                    });
	                
                    $('div.extension_group_titlebar', $tmpl).append($remove);
                }

                // Set html as title case eg: Image (from image)
                var title = this.options.labels[key] || (key.charAt(0).toUpperCase() + key.substr(1));
                $('span.extension_group_title', $tmpl).html(title);
            }

            // Create group title
            $('span.extension_group_title', $tmpl).attr('title', name).addClass(name);

            // add button action
            $('div.extension_list_add span', $tmpl).click( function() {            	
                self._createItem('custom').hide().prependTo($('ul.extension_list', $tmpl)).fadeIn('fast', function() {
                    var parent = this.parentNode;
                	
                    // Show scroll buttons
                    if (parent.firstChild.offsetHeight * parent.childNodes.length > parent.parentNode.offsetHeight) {
                        $(parent).parent('div.extension_list_container').next('div.extension_list_scroll_bottom').css('visibility', 'visible');
                    }
                    
                    $(this).focus();
                });
            });

            // Add scroll top button, click event and append
            $('div.extension_list_scroll_top', $tmpl).click( function() {
                self._scrollTo('top', $('ul.extension_list', $tmpl));
            });

            // Add scroll bottom button, click event and append
            $('div.extension_list_scroll_bottom', $tmpl).click( function() {
                self._scrollTo('bottom', $('ul.extension_list', $tmpl));
            });
            
            // cleanup list
            list = list.replace(/^[;,]/, '').replace(/[;,]$/, '');

            // Split value by comma to get individual file extensions and build list elements
            $.each(list.split(','), function() {
                $('ul.extension_list', $tmpl).append(self._createItem(this, key));
            });

            // Return container
            return $tmpl;
        },

        /**
         * Create a single list item
         * @param {String} value
         * @return {Object} Item Object
         */
        _createItem : function(value, group) {
            var self = this, v = value.replace(/[^a-z0-9]/gi, ''), $item;

            // Create input element if custom
            if (value == 'custom') {
                // custom item
                $item = $('<li class="file custom">'+
                    '	<span class="extension_title"><input type="text" value="" size="6" pattern="[a-zA-Z0-9_-]+" /></span>'+
                    //'	<span class="checkbox view" role="checkbox" aria-checked="false"></span>'+
                    '	<span class="extension_list_remove" role="button"></span>'+
                    '</li>');

                $('input', $item).change( function() {
                    if (this.value == '') {
                        $(this).removeClass('duplicate');
                        $($item).removeClass( function() {
                            return this.className.replace(/(file|custom)/, '');
                        });

                        return;
                    }

                    // Check for existing extension
                    if (new RegExp(new RegExp('[=,]' + this.value + '[,;]')).test($(self.element).val())) {
                        $(this).addClass('duplicate');
                        $item.addClass('duplicate');
                    } else {
                        $(this).removeClass('duplicate');
                        $item.removeClass( function() {
                            return this.className.replace(/(file|custom)/, '');
                        }).addClass(this.value);

                        // Trigger serialization
                        if (this.value != '') {
                            self._setValues();
                        }
                    }
                // add pattern validation
                }).focus().pattern();

            // Set title html
            } else {
                $item = $('<li class="file ' + v + '">' +
                    '	<span class="extension_title" title="' + value + '">' + value.replace(/[\W]+/, '') + '</span>'+
                    //'	<span class="checkbox view" role="checkbox" aria-checked="false"></span>'+
                    '	<span class="checkbox" role="checkbox" aria=checked="false"></span>'+
                    '</li>');
                
                var map = this.defaultMap[group];

                if ($.inArray(v, map) == -1) {
                    $('span.checkbox', $item).removeClass('checkbox').addClass('extension_list_remove').attr('role', 'button')
                } else {
                    $('span.checkbox', $item).addClass( function() {
                        return value.charAt(0) == '-' ? '' : 'checked';
                    }).attr('aria-checked', !(value.charAt(0) == '-')).click( function() {
                        if ($(this).is('.checked')) {
                            $(this).removeClass('checked').attr('aria-checked', false).prev('span.extension_title').attr('title', '-' + v);
                        } else {
                            $(this).addClass('checked').attr('aria-checked', true).prev('span.extension_title').attr('title', v);
                        }
                        // Trigger serialization
                        self._setValues();
                    });
                }
                
            /*$('span.checkbox.view', $item).addClass( function() {
                    return value.charAt(0) == '!' ? 'checked' : '';
                }).attr('aria-checked', (value.charAt(0) == '!')).click( function() {
                    var $title = $(this).prev('span.extension_title'), title = $title.attr('title').replace('^!', '');
                    
                    if ($(this).is('.checked')) {
                        $(this).removeClass('checked').attr('aria-checked', false);
                        $title.attr('title', title);
                    } else {
                        $(this).addClass('checked').attr('aria-checked', true);
                        
                        $title.attr('title', '!' + title);
                    }
                    // Trigger serialization
                    self._setValues();
                });*/

            }
            
            $('span.extension_list_remove', $item).click( function() {
                $item.fadeOut('fast', function() {   
                    var parent = this.parentNode;                      

                    // Show scroll buttons
                    if (parent.firstChild.offsetHeight * parent.childNodes.length < parent.parentNode.offsetHeight) {
                        $(parent).parent('div.extension_list_container').next('div.extension_list_scroll_bottom').css('visibility', 'hidden');
                    }
                        
                    if ($(parent).children().length == 0) {
                        $(parent).parents('div.extension_group_container').fadeOut('fast', function() {
                            $(this).remove();
                        });
                    }
                        
                    $(this).remove();
                    
                    // Trigger serialization
                    if ($('input', $item).val() == '') {
                        return;
                    }
                    
                    self._setValues();
                });

            });

            return $item;
        },

        /**
         * Show Scroll button
         * @param {Object} el UL element
         * @param {Array} dir Direction
         */
        _showScroll: function(el, dir) {
            var p = $(el).parent(), m = parseFloat($(el).css('margin-top'));

            function check(el, p, dir) {
                if (dir == 'top') {
                    return parseFloat(m) == 0;
                } else {
                    if (m == 0) {
                        var c = $(el).children();
                        return $(c).first().outerHeight() * c.length < $(p).outerHeight();
                    } else {
                        return (m + $(el).outerHeight()) < $(p).outerHeight();
                    }
                }
            }

            var scroll = (dir == 'top') ? p.prev() : p.next();
            $.each(dir, function(n, s) {
                if (check(el, p, s)) {
                    scroll.css('visibility', 'hidden');
                } else {
                    scroll.css('visibility', 'visible');
                }
            });

        },

        /**
         * Scroll list
         * @param {Object} dir Direction to scroll in
         * @param {Object} ul List element
         */
        _scrollTo: function(dir, ul) {
            var self = this, p = $(ul).parent(), mt = parseFloat($(ul).css('margin-top')), x = $(ul).get(0).firstChild.offsetHeight, v = mt - x, inv;

            if (dir == 'top') {
                v = mt + x;

                v = v + 1;

                if (mt == 0 || v > 0)
                    return;
            } else {
                v = v - 1;
            }

            inv = (dir == 'top') ? p.next() : p.prev();

            $(ul).animate({
                'marginTop': v
            }, 500, function() {
                $(inv).css('visibility', 'visible');
                self._showScroll(ul, [dir]);
            });

        },

        /**
         * Serialize lists and pass value to input element
         * @param {Object} input
         * @param {Object} container
         */
        _setValues: function() {
            var id = $(this.element).attr('id'), groups = [], title = '';

            // Iterate through child divs
            $('div.extension_group_container', '#' + id + '_mapper').each( function() {
                var n = $('span.extension_group_title:first', this);
                if ($(n).is('.custom')) {
                    title = $('input', n).val();
                } else {
                    title = $(n).attr('title');
                }

                if (title) {
                    var list = [], v, title = title.toLowerCase();
                    $('li span', this).each( function() {
                        v = $('input', this).val() || $(this).attr('title');

                        if (v) {
                            list.push(v);
                        }
                    });

                    // Add list to group
                    groups.push(title + '=' + list.join(','));
                }
            });
            
            var data = groups.join(';').replace(/([a-z]+)=;/g, '').replace(/^[;,]/, '').replace(/[;,]$/, '');

            // set value
            $(this.element).val(data).change();
        },

        destroy : function() {
            $.Widget.prototype.destroy.apply(this, arguments);
        }

    });
})(jQuery);