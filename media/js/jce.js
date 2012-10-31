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
    
    Joomla.modal = function(el, url, width, height) {
        var o = {
            'handler' : 'iframe',
            'size' : {
                x : width,
                y : height
            },
            'url' : url
        };
        
        if (typeof SqueezeBox.open === 'undefined') {
            return SqueezeBox.fromElement(el, o);
        }
        
        return SqueezeBox.open(el, o);
    };

    $.jce = {

        options : {},

        init : function(options) {
            var self = this;
            $.extend(true, this.options, options);

            // add ui-jce class to body
            $('body').addClass('ui-jce');
            
            /*$('input[type="checkbox"]').each(function() {
               $(this).wrap('<span class="ui-icon ui-icon-shadow ui-icon-checkbox" />'); 
            }).click(function() {
                $(this.parentNode).toggleClass('ui-icon-checkbox-on', this.checked);
            });*/
            
            $('a.dialog').click( function(e) { 
                self.createDialog(e, {
                    src 	: $(this).attr('href'),
                    options     : $(this).data('options')
                });
                    
                e.preventDefault();
            });
            
            // Bootstrap styles
            if (this.options.bootstrap) {
                // add boostrap id class
                $('body').addClass('ui-bootstrap');
                
                $('input[size="100"]').addClass('input-xlarge');
                $('input[size="50"]').addClass('input-large');
                $('input[size="5"]').addClass('input-mini');
                
            } else {
                $('body').addClass('ui-jquery');
                
                // Style stuff
                $('div.icon a').addClass('ui-widget-content ui-corner-all');
                
                $('button#filter_go').button({
                    icons: {
                        primary: 'ui-icon-search'
                    }
                });

                $('button#filter_reset').button({
                    icons : {
                        primary : 'ui-icon-arrowrefresh-1-e'
                    }
                });
                
                $('button.upload-import').button({
                    icons : {
                        primary : 'ui-icon-arrowthick-1-n'
                    }
                });

                // Table striping
                $('div#jce tbody tr:odd').addClass('odd');
            }

            // Tips
            $('.wf-tooltip').tips({
                parent : '#jce'
            });
            
            $('th input[type="checkbox"]', $('table.adminlist')).click(function() {
                var n = $('td input[type="checkbox"]', $('table.adminlist')).prop('checked', this.checked);
               
                $('input[name="boxchecked"]').val($(n).filter(':checked').length);
            });
            
            $('td input[type="checkbox"]', $('table.adminlist')).click(function() {
                var bc = $('input[name="boxchecked"]').val();
                var n  = $('td input[type="checkbox"]', $('table.adminlist')).length;
                
                $('th input[type="checkbox"]', $('table.adminlist')).prop('checked', bc == n);
            });

            // IE
            if (!$.support.cssFloat) {
                $('#jce').addClass('ie'); 

                // IE6 / IE7
                if (document.querySelector) {
                    // IE8
                    if (!$.support.leadingWhitespace) {
                        $('#jce').addClass('ie8');
                    // IE9
                    } else {
                        $('#jce').addClass('ie9');
                    }
                }
            }
            
            // set dependant parameters
            this._setDependants();

            // HTML5 style form elements
            this._formWidgets();
            
            $('label.radio').addClass('inline');
        },

        createDialog : function(e, o) {
            var self = this, data = {};

            // add optional settings from link
            if ($.type(o.options) == 'string') {
                data = $.parseJSON(o.options.replace(/'/g, '"'));
            } else {
                data = o.options;
            }
            
            data = data || {width : 640, height : 480};
            
            return Joomla.modal(e.target, o.src, data.width, data.height);
        },
        
        closeDialog : function(el) {
            //$(el).dialog("close").remove();
            
            var win = window.parent;
            
            // try squeezebox
            if( typeof win.SqueezeBox !== 'undefined') {
                return win.SqueezeBox.close();
            }
        },

        /**
         * Password input
         */
        _passwordWidget : function(el) {
            var span = document.createElement('span');

            $(span).addClass('widget-password locked').insertAfter(el).click( function() {
                el = $(this).siblings('input[type="password"]');

                if ($(this).hasClass('locked')) {
                    var input = document.createElement('input');

                    $(el).hide();

                    $(input).attr({
                        type 	: 'text',
                        size 	: $(el).attr('size'),
                        value	: $(el).val(),
                        'class' : $(el).attr('class')
                    }).insertAfter(el).change( function() {
                        $(el).val(this.value);
                    });

                } else {
                    var n = $(this).siblings('input[type="text"]');
                    var v = $(n).val();
                    $(n).remove();
                    $(el).val(v).show();
                }
                $(this).toggleClass('locked');
            });

        },

        /**
         * HTML5 form widgets
         */
        _formWidgets : function() {
            var self = this;

            $('input[type="password"]').each( function() {
                self._passwordWidget(this);
            });

            $('input[placeholder]:not(:file), textarea[placeholder]').placeholder();

            $(':input[pattern]').pattern();

            $(':input[max]').max();

            $(':input[min]').min();
        },
        
        _setDependants : function() {
            $('[data-parent]').each(function() {
                var el = this, data = $(this).data('parent');
                
                // hide the element by default
                $(this).parent().hide();
                
                // get the parent selector and value
                var s = /([\w\.]+)\[([\w,]+)\]/.exec(data);
                
                if (s) {
                    var  k = s[1], v = s[2].split(',');
 
                    // set parent onchange
                    $('#params' + k.replace(/[^\w]+/g, '')).change(function() {
                        var state = $.inArray(this.value, v) != -1;                        

                        if (state) {
                            $(el).parent().show();
                        } else {
                            $(el).parent().hide();
                        }
                        
                        $(el).trigger('visibility:toggle', state);
                    // set function when element is toggled itself    
                    }).on('visibility:toggle', function(e, state) {
                        if (state) {
                            $(el).parent().show();
                        } else {
                            $(el).parent().hide();
                        }
                    }).change();
                }
            });
        }
    };    
})(jQuery);
// global shortcut
var $jce = jQuery.jce;