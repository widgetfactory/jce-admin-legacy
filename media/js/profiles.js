/**
 * @version		$Id: profiles.js 226 2011-06-13 09:59:05Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    // Create Profiles object
    $.jce.Profiles = {
        init : function(options) {
            var self = this;
            
            if ($.browser.msie) {
                $('#jce').addClass('ie');
            }

            // ie7 flag
            if (!$.support.cssFloat && !!window.XMLHttpRequest && !document.querySelector) {
                $('#jce').addClass('ie7');
            }

            // Tabs
            $('#tabs').tabs();

            // Buttons
            $('button#user-group-add').button({
                icons : {
                    primary : 'icon-add'
                }
            }).click( function(e) {
                e.preventDefault();
                $('select#types').children().attr('selected', true);
                return false;
            });

            $('button#user-group-remove').button({
                icons : {
                    primary : 'icon-remove'
                }
            }).click( function(e) {
                e.preventDefault();
                $('select#types').children(':selected').attr('selected', false);
                return false;
            });

            // users list
            $('button#users-add').button({
                icons : {
                    primary : 'icon-add'
                }
            });

            $('button#users-remove').button({
                icons : {
                    primary : 'icon-remove'
                }
            }).click( function(e) {
                e.preventDefault();
                $('select#users').children(':selected').remove();
                return false;
            });

            $('button#layout-legend').button({
                icons : {
                    primary : 'icon-legend'
                }
            });

            // Editable Selects

            $( "select.editable, select.combobox" ).combobox(options.combobox);

			// Editor Tabs
			$("#tabs-editor").tabs().addClass('ui-tabs-vertical ui-helper-clearfix');

            $("#tabs-plugins").tabs({
            	selected : -1
            }).addClass('ui-tabs-vertical ui-helper-clearfix');
            
            // make vertical tabs
            $("#tabs-editor > ul > li, #tabs-plugins #tabs-editor > ul > li").removeClass('ui-corner-top').addClass('ui-corner-left');
            
            $("#tabs-plugins").tabs('select', $('ul.ui-tabs-nav > li.ui-state-default:not(.ui-state-disabled):first', '#tabs-plugins').index());

            // Color Picker
            $('input.color').colorpicker(options.colorpicker);

            // Extension Mapper
            $('select.extensions, input.extensions, textarea.extensions').extensionmapper(options.extensions);

            // Layout
            this.createLayout();

            // Browser
            $('input.browser').each( function() {
                var el = this;

                $('<span class="browser"></span>').click( function() {
                    var src = 'index.php?option=com_jce&view=editor&layout=plugin&plugin=browser&standalone=1&element=' + $(el).attr('id');
                    if ($(el).data('filter')) {
                    	src += '&filter=' + $(el).data('filter');
                    }
                    $.jce.createDialog($.extend(options.dialog, {
                        src 	: src,
                        options : {
                            'width'	:785,
                            'height':450
                        },
                        modal	: true,
                        type	: 'browser',
                        id		: $(el).attr('id') + '_browser',
                        title 	: options.browser.title || 'Browser'
                    }));
                }).insertAfter(this);

            });

            // Check list
            $('select.checklist, input.checklist').checkList();

            $('input.autocomplete').each( function() {
                var el = this, v = $(el).attr('placeholder') || '';
                $(el).removeAttr('placeholder');
                $(el).autocomplete({
                    source: v.split(',') || []
                });
            });

            $('input[name="components-select"]').click( function() {
                $('select#components').attr('disabled', (this.value == 'all')).children('option:selected').removeAttr('selected');
            });

            $('#paramseditorwidth').change( function() {
                var v = $(this).val();
                
                if (v && /%/.test(v)) {
                    return;
                } else {
                    if (v) {
                        v = parseInt(v);
                    } else {
                        v = 600;
                    }
                    $('span.widthMarker', '#profileLayoutTable').width(v).children('span').html(v + 'px');
                }
            });

            $('ul#profileAdditionalFeatures input:checkbox').click( function() {
                self.setPlugins();
            });
        },

        onSubmit : function() {
            $('div#tabs-editor, div#tabs-plugins').find(':input[name]').each( function() {
                // disable blank or placeholder values
                if ($(this).val() === '' || $(this).hasClass('placeholder')) {
                    $(this).attr('disabled', 'disabled');
                }
            });

        },

        createLayout : function() {
            var self = this;

            // List items
            $("ul.sortableList").sortable({
                connectWith	: 'ul.sortableList',
                axis		: 'y',
                tolerance	: 'intersect',
                handle		: 'span.sortableHandle',
                update		: function(event, ui) {
                    self.setRows();
                    self.setPlugins();
                },
                start : function(event, ui) {
                	$(ui.placeholder).width($(ui.item).width());
                },
                placeholder	: 'sortableListItem ui-state-highlight'
            }).disableSelection();
            
            $('span.sortableOption', 'ul.sortableList li').hover(function() {
            	$(this).append('<span role="button"/>');
            }, function() {
            	$(this).empty();
            }).click(function() {
            	var $parent = $(this).parent();
            	var $target = $('ul.sortableList', '#profileLayoutTable').not($parent.parent());
            	$parent.hide().appendTo($target).show('slow');
            	
            	$(this).empty();
            });

            $('ul.sortableRow').sortable({
                connectWith	: 'ul.sortableRow',
                tolerance	: 'intersect',
                update: function(event, ui) {
                    self.setRows();
                    self.setPlugins();
                },
                start : function(event, ui) {
                	$(ui.placeholder).width($(ui.item).width());
                },
                placeholder	: 'sortableRowItem ui-state-highlight'
            }).disableSelection();
        },

        setRows : function() {
            var rows = [];

            $('ul.sortableRow:has(li)', '#profileLayout').each( function() {
                rows.push($.map($('li.sortableRowItem', $(this)), function(el) {
                    if ($(el).hasClass('spacer')) {
                        return 'spacer';
                    }
                    return $(el).data('name');
                }).join(','));

            });

            $('input[name="rows"]').val(rows.join(';'));
        },

        /**
         * show / hide parameters for each plugin
         * @param {Object} id
         * @param {Object} state
         */
        setPlugins: function() {
            var self = this, plugins = [];

            $('ul.sortableRow li.plugin', '#profileLayout').each( function() {
                plugins.push($(this).data('name'));
            });

            $('ul#profileAdditionalFeatures input:checkbox:checked').each( function() {
                plugins.push($(this).val());
            });

            // set plugins
            $('input[name="plugins"]').val(plugins.join(','));

            self.setParams(plugins);
        },

        setParams : function(plugins) {
            var $tabs = $('div#tabs-plugins');

            $('div.ui-tabs-panel', 'div#tabs-plugins').each( function(i) {
                var name = $(this).data('name');

                var s = $.inArray(name, plugins) != -1;
                // disable forms in tab panel
                $(':input[name]', $(this)).prop('disabled', !s);

                if (!s) {
                    if ($tabs.tabs('option', 'selected') == i) {
                        var n = 0, x = $tabs.tabs('option', 'disabled');

                        while (i == n) {
                            n++;

                            if ($.inArray(n, x) != -1) {
                                n++;
                            }
                        }

                        // select another tab if current tab is this one
                        $tabs.tabs('select', n);
                    }

                    // disable the tabs
                    $tabs.tabs('disable', i);

                } else {
                    $tabs.tabs('enable', i);
                }
            });

        }

    };
    // End Groups
})(jQuery);