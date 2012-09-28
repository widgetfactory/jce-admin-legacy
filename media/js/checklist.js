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
    $.fn.checkList = function(options) {
        this.each( function() {
            return $.CheckList.init(this, options);
        });
    };

    $.CheckList = {
        options: {
            valueAsClassName: false,
            onCheck         : $.noop
        },
        /**
         * Initilaise plugin
         * @param {Object} elements Select elements to process
         * @param {Object} options Options object
         */
        init: function(el, options) {
            var self = this;
            $.extend(this.options, options);

            var ul 	= document.createElement('ul');
            var elms 	= [];

            if (el.nodeName == 'SELECT') {
                $.each($('option', el), function() {
                    elms.push({
                        name		: $(this).html(),
                        value		: $(this).val(),
                        selected 	: $(this).prop('selected'),
                        disabled        : $(this).prop('disabled')
                    });
                });

            } else {
                $.each(el.value.split(','), function() {
                    elms.push({
                        name	: this,
                        value	: this
                    });
                });
            }

            // hide element
            $(el).hide();

            $(ul).addClass('widget-checklist').insertBefore(el);
            
            if ($(el).hasClass('buttonlist')) {
                $(ul).wrap('<div class="defaultSkin buttonlist" />');
            }

            $.each(elms, function() {
                self.createElement(el, ul, this);
            });

            if ($(el).hasClass('sortable')) {
                $(ul).addClass('sortable').sortable({
                    axis: 'y',
                    tolerance: 'intersect',
                    update: function(event, ui) {
                        self.setValue(el, $(ui.item).parent());
                    },
                    placeholder: "ui-state-highlight"

                }).disableSelection();
            }
        },

        createElement: function(el, ul, n) {
            // Create elements
            var self = this, d = document, li = d.createElement('li'), check = d.createElement('span'), plugin;

            $(li).attr({
                title: n.value
            }).addClass('ui-widget-content ui-corner-all').appendTo(ul);
            
            if ($(el).hasClass('buttonlist')) {
                // get the plugin name
                var name = el.name, s = name.split(/[^\w]+/);
            
                if (s && s.length > 1) {
                    plugin = s[1];
                } 
            }
            
            var $toolbar    = $('span.profileLayoutContainerToolbar ul', '#profileLayoutTable');
            
            if (plugin) {
                var $parent = $('span[data-name="' + plugin + '"]', $toolbar);
            }

            // Add checkboxes
            $(check).addClass('checkbox').addClass( function() {
                return n.selected ? 'checked' : '';
            }).click( function() {
                
                if ($(this).hasClass('disabled')) {
                    return;
                } 
                // add check and trigger
                $(this).toggleClass('checked').trigger('checkbox:check', $(this).hasClass('checked'));
            }).appendTo(li).on('checkbox:check', function(e, state) {             
                // Trigger serialization
                self.setValue(el, ul);
                
                // if button list and plugin name set
                if (plugin) {
                    $('span.mce_' + n.value, $parent).parent().toggle(state);
                }
                
                // trigger callback
                self.options.onCheck.call(self, [this, n]);
            });
            
            // initialise
            $(check).trigger('checkbox:check', $(check).hasClass('checked'));
            
            // disable
            if (n.disabled) {
                $(check).addClass('disabled');
            }

            // Add name
            $(li).append('<span class="widget-checklist-' + n.value + '" title="' + n.name + '">' + n.name + '</span>');

                        
            if ($(el).hasClass('buttonlist')) {                
                $('span.widget-checklist-' + n.value, li).prepend('<span class="mceButton mceSplitButton"><span class="mceIcon mce_' + n.value + '"></span></span>');
            }
        },

        setValue: function(el, ul) {
            var $list = $('li', ul);

            var x = $.map($('span.checked', $list), function(n) {
                return $(n).parent('li').attr('title');
            });

            if (el.nodeName == 'SELECT') {
                $(el).empty();                
                
                $.each($list, function(i, item) {
                    var v = $(item).attr('title');
                    var o = document.createElement('option');
                	
                    $(o).attr({
                        'value' : v
                    }).prop('selected', !($.inArray(v, x) == -1)).appendTo(el);
                });                
            } else {
                el.value = x.join(',');
            }
        }
    };
})(jQuery);