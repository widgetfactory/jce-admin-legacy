/**
 * @version		$Id: checklist.js 222 2011-06-11 17:32:06Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
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
            valueAsClassName: false
        },
        /**
         * Initilaise plugin
         * @param {Object} elements Select elements to process
         * @param {Object} options Options object
         */
        init: function(el, options) {
            var self = this;
            $.extend(this.options, options);

            var ul 		= document.createElement('ul');
            var elms 	= [];

            if (el.nodeName == 'SELECT') {
                $.each($('option', el), function() {
                    elms.push({
                        name		: $(this).html(),
                        value		: $(this).val(),
                        selected 	: $(this).prop('selected')
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
            var self = this, d = document, v, s, li = d.createElement('li'), label = d.createElement('label'), check = d.createElement('span');

            $(li).attr({
                title: n.value
            }).addClass('ui-widget-content ui-corner-all').appendTo(ul);

            $(li).addClass(v);

            // Add checkboxes
            $(check).addClass('checkbox').addClass( function() {
                return n.selected ? 'checked' : '';
            }).click( function() {
                $(this).toggleClass('checked');
                // Trigger serialization
                self.setValue(el, ul);
            }).appendTo(li);

            // Add name
            $(li).append('<span class="widget-checklist-' + n.value + '">' + n.name + '</span>');
        },

        setValue: function(el, ul) {
        	$list = $('li', ul);

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