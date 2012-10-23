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
    
    $.jce = {

        options : {},

        init : function(options) {
            var self = this;
            $.extend(true, this.options, options);

            // add ui-jce class to body
            $('body').addClass('ui-jce');
            
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

                $('a.dialog').click( function(e) {                
                    self.createDialog({
                        src 	: $(this).attr('href'),
                        options     : $(this).data('options'),
                        modal	: $(this).hasClass('modal'),
                        type	: /(users|help|preferences|updates|browser|legend)/.exec($(this).attr('class'))[0],
                        title	: $(this).attr('title')
                    });
                    e.preventDefault();
                });
                
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

        createDialog : function(o) {
            var self = this;
            function _fixDialog(el, settings) {
                // opera bug?
                if (parseFloat(el.style.height) == 0) {
                    var h = settings.height;
                    // get height of siblings
                    $(el).siblings('div').each( function() {
                        h = h - parseFloat($(this).outerHeight());
                    });

                    // remove border, padding etc.
                    h = h - $(el).outerHeight();
                    // set height and centre
                    $(el).css('height', h).dialog('option', 'position', 'center');
                }
            }

            var buttons = {};
            var div		= document.createElement('div');
            var loader	= document.createElement('div');
            var iframe 	= document.createElement('iframe');
            var title 	= o.title || '';

            if (o.type == 'users') {
                $.extend(buttons, {
                    '$select' : function() {
                        iframe.contentWindow.selectUsers();
                        $(this).dialog("close");
                    }

                });
            }

            if (o.type == 'preferences') {
                $.extend(buttons, {
                    '$save' : function() {
                        iframe.contentWindow.submitform('apply');
                    },

                    '$saveclose' : function() {
                        iframe.contentWindow.submitform('save');
                    }

                });
            }

            var src = o.src, data = {};

            // add optional settings from link
            if ($.type(o.options) == 'string') {
                data = $.parseJSON(o.options.replace(/'/g, '"'));
            } else {
                data = o.options;
            }
            
            data = data || {};

            var settings = {
                bgiframe: true,
                width 	: 640,
                height	: 480,
                modal	: o.modal || false,
                buttons : buttons,
                resizable: true,
                open : function() {
                    _fixDialog(div, settings);
                    $(loader).addClass('loader').appendTo(div);

                    $(iframe).css({
                        width : '100%',
                        height : '100%'
                    }).attr({
                        src 		: src,
                        scrolling 	: 'auto',
                        frameBorder : 'no'
                    }).appendTo(div).load( function() {
                        $(loader).hide();
                    });

                    $('button').each( function() {
                        var h = this.innerHTML;
                        h = h.replace(/\$([a-z]+)/, function(a, b) {
                            return self.options.labels[b];
                        });

                        this.innerHTML = h;
                    }).button();

                }

            };

            if (o.type == 'confirm' || o.type == 'alert') {
                var text 	= o.text 	|| '';
                var title 	= o.title 	|| (o.type == 'alert' ? self.options.labels.alert : '');

                $.extend(settings, {
                    width 		: 300,
                    height		: 'auto',
                    resizable	: false,
                    dialogClass	: 'ui-jce',
                    buttons : {
                        '$ok' : function() {
                            if (src) {
                                if (/function\([^\)]*\)\{/.test(src)) {
                                    $.globalEval(src);
                                } else {
                                    document.location.href = src;
                                }	
                            }
                            $(this).dialog("close");
                        }
                    },
                    open : function() {
                        _fixDialog(div, settings);

                        $(div).attr('id', 'dialog-confirm').append(text);

                        $('button').each( function() {
                            var h = this.innerHTML;
                            h = h.replace(/\$([a-z]+)/, function(a, b) {
                                return self.options.labels[b];
                            });

                            this.innerHTML = h;
                        }).addClass('ui-state-default ui-corner-all');

                    },
                    
                    close : function() {
                        $(this).dialog('destroy');
                    }

                });
                
                if (o.type == 'confirm') {
                    $.extend(settings.buttons, {
                        '$cancel' : function() {
                            $(this).dialog("close");
                        }
                    });
                }
            }
            
            // add id if set
            if (data.id) {
                $(div).attr('id', data.id);
            }

            $(div).css('overflow', 'hidden').attr('title', title).dialog($.extend(settings, data));
        },
        
        closeDialog : function(el) {
            $(el).dialog("close").remove();
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