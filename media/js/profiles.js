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
    
    /*$.support.multipleBackground = function() {
        var div = document.createElement('div');
        
        $(div).css('background:url(https://),url(https://),red url(https://)');
        return (/(url\s*\(.*?){3}/).test(div.background);
    };*/
    
    // Create Profiles object
    $.jce.Profiles = {
        
        options : {},
        
        init : function(options) {
            var self = this;
            
            $.extend(true, this.options, options);
            
            var dir = $('body').css('direction') == 'rtl' ? 'right' : 'left';
            
            if ($('body').hasClass('ui-bootstrap')) {                  
                // Editor Tabs
                $("#tabs-editor > ul.nav-tabs li a:first").tab('show');
                // Plugin tabs
                $("#tabs-plugins > ul.nav-tabs li a:first").tab('show');
                
            } else {
                // users list
                $('a#users-add').button({
                    icons : {
                        primary : 'ui-icon-person'
                    }
                });
                
                $("#tabs-editor").tabs({
                    selected : -1
                }).addClass('ui-tabs-vertical ui-helper-clearfix');
                
                $("#tabs-plugins").tabs({
                    'activate' : $('ul.ui-tabs-nav > li.ui-state-default:not(.ui-state-disabled):first', '#tabs-plugins').index()
                }).addClass('ui-tabs-vertical ui-helper-clearfix');
                
                // make vertical tabs
                $("#tabs-editor ul.ui-tabs-nav > li, #tabs-plugins ul.ui-tabs-nav > li").removeClass('ui-corner-top').addClass('ui-corner-' + dir);
            }

            $('input.checkbox-list-toggle-all').click(function() {                                                
                $('input[type="checkbox"]', '#user-groups').prop('checked', this.checked).trigger('check');
            });
            
            // Components select
            $('input[name="components-select"]').click( function() {
                $('input[type="checkbox"]', '#components').prop('disabled', (this.value == 'all')).trigger('disable').filter(':checked').prop('checked', false).trigger('check');
            });

            // Editable Selects

            $( "select.editable, select.combobox" ).combobox(options.combobox);
            
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
            
            $('input.plugins-enable-checkbox').on('click', function() {
                var s = this.checked, name = $(this).data('name'), proxy = $(this).next('input[type="hidden"]');
                
                // check for proxy...
                if ($(proxy).length == 0) {
                    // create proxy
                    proxy = $('<input type="hidden" name="' + $(this).attr('name') + '" />').insertAfter(this);
                    // remove attribute
                    $(this).removeAttr('name');
                }

                // set value for proxy and trigger change                
                $(proxy).val(s ? 1 : 0).change();
                
                // disable default select and reset value
                $('select.plugins-default-select', $(this).parents('fieldset:first')).children('option[value="' + name + '"]').prop('disabled', !s).parent().val(function(i, v) {                                        
                    if (v === name) {
                        return "";
                    }
                    
                    return v;
                });
            });
            
            /*if ($.support.multipleBackground) {
                $('#jce').addClass('multiplebg');          
            } else {
                // fix for CSS3 selectors
                $('span.mceSplitButton span.mceIcon').not('span.mceIconLayer').after('<span/>');
            }*/
            
        // custom checkbox
        //$('input[type="checkbox"]').checkbox();
        // custom radio
        //$('input[type="radio"]').radio();
        },
        
        validate : function() {
            var required = [];
        	
            $(':input.required').each(function() {
                if ($(this).val() === '') {
                    var parent = $(this).parents('div.tab-pane').get(0);
                    
                    required.push("\n" + $('#tabs ul li a[href=#' + parent.id + ']').html() + ' - ' + $.trim($('label[for="' + this.id + '"]').html()));
                }
            });
        	
            if (required.length) {
                var msg = $.jce.options.labels.required;
                msg += required.join(',');
        		
                alert(msg);
      		
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
                start : function(event, ui) {
                    $(ui.placeholder).width($(ui.item).width());
                },
                placeholder : 'sortableListItem sortable-highlight',
                opacity : 0.8
            }).disableSelection();
            
            $('span.sortableOption').hover(function() {
                $(this).append('<span role="button"/>');
            }, function() {
                $(this).empty();
            }).click(function() {
                var $parent     = $(this).parents('li.sortableListItem').first();
                var $target     = $('ul.sortableList', '#profileLayoutTable').not($parent.parent());
                
                $parent.appendTo($target);
            	
                $(this).empty();
            	
                self.setRows();
                self.setPlugins();
            });

            $('span.sortableRow').sortable({
                connectWith	: 'span.sortableRow',
                tolerance	: 'pointer',
                update: function(event, ui) {
                    self.setRows();
                    self.setPlugins();
                    
                    self._fixLayout();
                },
                start : function(event, ui) {
                    $(ui.placeholder).width($(ui.item).width());
                },
                opacity : 0.8,
                placeholder	: 'sortableRowItem sortable-highlight'
            }).disableSelection();
            
            this._fixLayout();
        },

        setRows : function() {
            var rows = [];

            $('span.sortableRow:has(span)', '#toolbar_container').each( function() {
                rows.push($.map($('span.sortableRowItem', this), function(el) {
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

            $('span.sortableRow span.plugin', '#toolbar_container').each( function() {
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
            var $tabs = $('div#tabs-plugins > ul.nav.nav-tabs > li');

            $tabs.removeClass('tab-disabled ui-state-disabled').removeClass('active ui-tabs-active ui-state-active').each( function(i) {
                var name = $(this).data('name');

                var s = $.inArray(name, plugins) != -1;
                // disable forms in tab panel
                $('input[name], select[name]', this).prop('disabled', !s);

                if (!s) {                    
                    $(this).addClass('tab-disabled');
                }
            });
            
            $tabs.not('.tab-disabled').first().addClass('active ui-tabs-active ui-state-active');
        }

    };
// End Groups
})(jQuery);