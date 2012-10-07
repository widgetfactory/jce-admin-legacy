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
    if (typeof Joomla === 'undefined') {
        Joomla = {};
    }
	
    Joomla.submitbutton = submitbutton = function(button) {
        // Cancel button
        if (button == "cancelEdit") {
            try {
                Joomla.submitform(button);
            } catch(e) {
                submitform(button);
            }

            return;
        }
		
        if ($jce.Profiles.validate()) {
            $jce.Profiles.onSubmit();
            try {
                Joomla.submitform(button);
            } catch(e) {
                submitform(button);
            }
        }
    };
    
    // Create Profiles object
    $.jce.Profiles = {
        
        options : {},
        
        init : function(options) {
            var self = this;
            
            $.extend(true, this.options, options);

            // Tabs
            $('#tabs').tabs();
            
            $('input.checkbox-list-toggle-all').click(function() {                
                $('input', this.parentNode.parentNode).prop('checked', this.checked);
            });
            
            // Components select
            $('input[name="components-select"]').click( function() {
                $('input[type="checkbox"]', '#components').prop('disabled', (this.value == 'all')).filter(':checked').prop('checked', false);
            });

            // users list
            $('a#users-add').button({
                icons : {
                    primary : 'icon-add'
                }
            });

            $('a#users-remove').button({
                icons : {
                    primary : 'icon-remove'
                }
            }).click( function(e) {
                e.preventDefault();
                $('select#users').children(':selected').remove();
                return false;
            });

            $('a#layout-legend').button({
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
            
            var dir = $('body').css('direction') == 'rtl' ? 'right' : 'left';
            
            // make vertical tabs
            $("#tabs-editor ul.ui-tabs-nav > li, #tabs-plugins ul.ui-tabs-nav > li").removeClass('ui-corner-top').addClass('ui-corner-' + dir);
            
            $("#tabs-plugins").tabs('select', $('ul.ui-tabs-nav > li.ui-state-default:not(.ui-state-disabled):first', '#tabs-plugins').index());

            // Color Picker
            $('input.color').colorpicker(options.colorpicker);

            // Extension Mapper
            $('select.extensions, input.extensions, textarea.extensions').extensionmapper(options.extensions);

            // Layout
            this.createLayout();

            // Browser
            /*$('input.browser').each( function() {
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

            });*/

            // Check list
            $('select.checklist, input.checklist').checkList({
                onCheck : function() {
                    self.setRows();
                }
            });

            $('input.autocomplete').each( function() {
                var el = this, v = $(el).attr('placeholder') || '';
                $(el).removeAttr('placeholder');
                $(el).autocomplete({
                    source: v.split(',') || []
                });
            });


            $('#paramseditorwidth').change( function() {
                var v = $(this).val() || 600, s = v + 'px';
                
                if (/%/.test(v)) {
                    s = v, v = 600;
                } else {
                    v = parseInt(v), s = v + 'px';
                }
                
                $('span.widthMarker span', '#profileLayoutTable').html(s);
                    
                $('#editor_container').width(v);
                $('span.widthMarker, #statusbar_container span.mceStatusbar').width(v);
            });
            
            $('#paramseditorheight').change( function() {
                var v = $(this).val() || 'auto';
                
                if (/%/.test(v)) {
                    v = 'auto';
                } else {
                    if ($.type(v) == 'number') {
                        v = parseInt(v);
                    }
                }
                
            //$('span.profileLayoutContainerEditor', '#profileLayoutTable').height(v);
            });
            
            // Toolbar Theme
            $('#paramseditortoolbar_theme').change( function() {
                var v = $(this).val();

                if (v.indexOf('.') != -1) {
                    v = v.split('.');
                    var s = v[0] + 'Skin';
                    var c = v[1];
            			
                    v = s + ' ' + s + c.charAt(0).toUpperCase() + c.substring(1);
                } else {
                    v += 'Skin';
                }
            	
                $('span.profileLayoutContainer').each(function() {
                    var cls = this.className;
                    cls = cls.replace(/([a-z0-9]+)Skin([a-z0-9]*)/gi, '');
            		
                    this.className = $.trim(cls);
                }).addClass(v);
            });
            
            // Toolbar Alignment
            $('#paramseditortoolbar_align').change( function() {
                
                var v = $(this).val();
                $('ul.sortableList', '#toolbar_container').removeClass('mceLeft mceCenter mceRight').addClass('mce' + v.charAt(0).toUpperCase() + v.substring(1));    
                
                self._fixLayout();
            }).change();
            
            // Editor Path
            $('#paramseditorpath').change( function() {
                $('span.mceStatusbar span.mcePathLabel').toggle($(this).val() == 1);
            }).change();

            // Additional Features
            $('ul#profileAdditionalFeatures input:checkbox').click( function() {
                self.setPlugins();
            });

            // toolbar position
            $('#paramseditortoolbar_location').change(function() {
                var $after = $('#editor_container');
            	
                if ($(this).val() == 'top') {
                    $after = $('span.widthMarker');
                }
            	
                $('#toolbar_container').insertAfter($after);
            }).change();
            
            // toolbar location
            $('#paramseditorstatusbar_location').change(function() {
                var v = $(this).val();
                // show statusbar by default
                $('#statusbar_container').show();
            	
                // hide statusbar
                if (v == 'none') {
                    $('#statusbar_container').hide();
                }

                var $after = $('#editor_container');
            	
                if (v == 'top') {
                    $after = $('span.widthMarker');
            		
                    if ($('#paramseditortoolbar_location').val() == 'top') {
                        $after = $('#toolbar_container');
                    }
                }

                $('#statusbar_container').insertAfter($after);
            }).change();
            
            // resizing
            $('#paramseditorresizing').change(function() {
                var v = $(this).val();
                // show statusbar by default
                $('a.mceResize', '#statusbar_container').toggle(v == 1);
            }).change();
            
            // toggle
            $('#paramseditortoggle').change(function() {
                var v = $(this).val();
                // show statusbar by default
                $('#editor_toggle').toggle(v == 1);
            }).change();
            
            $('#paramseditortoggle_label').on('change keyup', function() {
                if (this.value) {
                    // show statusbar by default
                    $('#editor_toggle').text(this.value); 
                }
            });
            
            $('#users').click(function(e) {
                var n = e.target;
                
                if ($(n).is('span.users-list-delete')) {
                    $(n).parent().parent().remove();
                }
            });
            
            $('input:checkbox.plugins-enable-checkbox').click(function() {
                var p = this.parentNode.parentNode, s = this.checked, name = $(this).data('name');
                // set value for proxy onput and trigger change                
                $(this).prev().val(s ? 1 : 0).change();
                // disable select
                $('select.plugins-default-select', p).children('option[value="' + name + '"]').prop('disabled', !s);
            });
        },
        
        validate : function() {
            var required = [];
        	
            $(':input.required').each(function() {
                if ($(this).val() === '') {
                    required.push('<li>' + $('label[for="' + this.id + '"]').html() + '</li>');
                }
            });
        	
            if (required.length) {
                var msg = '<p>' + $jce.options.labels.required + '</p>';
                msg += '<ul>';
                msg += required.join('');
                msg += '</ul>';
        		
                $jce.createDialog({
                    type  : 'alert',
                    text  : msg,
                    modal : true
                });
      		
                return false;
            }
			
            return true;
        },

        onSubmit : function() {
            // select all users
            //$('option', '#users').prop('selected', true);
            
            $('div#tabs-editor, div#tabs-plugins').find(':input[name]').each( function() {
                // disable placeholder values
                if ($(this).hasClass('placeholder')) {
                    $(this).attr('disabled', 'disabled');
                }
            });
        },
        
        _fixLayout : function() {
            $('span.mceButton, span.mceSplitButton').removeClass('mceStart mceEnd');
            
            // fix for buttons before or after lists
            $('span.mceListBox').parent('span.sortableRowItem').prev('span.sortableRowItem').children('span.mceButton:last, span.mceSplitButton:last').addClass('mceEnd');
            $('span.mceListBox').parent('span.sortableRowItem').next('span.sortableRowItem').children('span.mceButton:first, span.mceSplitButton:first').addClass('mceStart');
        },

        createLayout : function() {
            var self = this;

            // List items
            $("ul.sortableList").sortable({
                connectWith	: 'ul.sortableList',
                axis		: 'y',
                update		: function(event, ui) {
                    self.setRows();
                    self.setPlugins();
                },
                placeholder	: 'sortableListItem ui-state-highlight'
            }).disableSelection();
            
            $('span.sortableOption').hover(function() {
                $(this).append('<span role="button"/>');
            }, function() {
                $(this).empty();
            }).click(function() {
                var $parent = $(this).parent();
                var $target = $('ul.sortableList', '#profileLayoutTable').not($parent.parent());
                $parent.hide().appendTo($target).show('slow');
            	
                $(this).empty();
            	
                self.setRows();
                self.setPlugins();
            });

            $('div.sortableRow').sortable({
                connectWith	: 'div.sortableRow',
                tolerance	: 'pointer',
                update: function(event, ui) {
                    self.setRows();
                    self.setPlugins();
                    
                    self._fixLayout();
                },
                start : function(event, ui) {
                    $(ui.placeholder).width($(ui.item).width());
                },
                placeholder	: 'sortableRowItem ui-state-highlight'
            }).disableSelection();
            
            if (!$.support.leadingWhitespace) {
                // fix for CSS3 selectors
                
                $('.mceSplitButton .mceIcon').not('.mceIconLayer').each(function() {                   
                    $('<span/>').insertAfter(this);
                });              
            } else {
                $('#jce').addClass('multiplebg');
            }
            
            this._fixLayout();
        },

        setRows : function() {
            var rows = [];

            $('div.sortableRow:has(span)', '#toolbar_container').each( function() {
                rows.push($.map($('span.sortableRowItem:visible', $(this)), function(el) {
                    return $(el).data('name');
                }).join(','));
            });

            $('input[name="rows"]').val(rows.join(';'));
        },
        
        setLayout : function() {    
            var $spans = $('span.profileLayoutContainerCurrent > span').not('span.widthMarker');
        	
            $.each(['toolbar', 'editor', 'statusbar'], function() {
                $('#paramseditor' + this + '_location').val($spans.index($('#' + this + '_container')));
            });
        },

        /**
         * show / hide parameters for each plugin
         * @param {Object} id
         * @param {Object} state
         */
        setPlugins: function() {
            var self = this, plugins = [];

            $('div.sortableRow span.plugin', '#toolbar_container').each( function() {
                plugins.push($(this).data('name'));
            });

            $('ul#profileAdditionalFeatures input:checkbox:checked').each( function() {
                plugins.push($(this).val());
            });

            // set plugins
            $('input[name="plugins"]').val(plugins.join(','));

            this.setParams(plugins);
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