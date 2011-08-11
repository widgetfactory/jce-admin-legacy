/**
 * @version		$Id: jce.js 258 2011-07-01 09:19:09Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce = {

        options : {},

        init : function(options) {
            var self = this;
            $.extend(true, this.options, options);

            // add ui-jce class to body
            $('body').addClass('ui-jce');

            // Tips
            $('.hasTip').tips({
                parent : '#jce'
            });

            // Style stuff
            $('div.icon a').addClass('ui-widget-content ui-corner-all');

            $('a.dialog').click( function(e) {
                self.createDialog({
                    src 	: $(this).attr('href'),
                    options : $(this).data('options'),
                    modal	: $(this).hasClass('modal'),
                    type	: /(users|help|preferences|updates|browser|legend)/.exec($(this).attr('class'))[0],
                    title	: $(this).attr('title')
                });
                e.preventDefault();
            });

            // IE
            if (!$.support.cssFloat) {
                // IE6
                if (!window.XMLHttpRequest) {
                    $('input:text').addClass('ie_input_text');

                    $('ul.adminformlist > li, dl.adminformlist > dd').addClass('ie_adminformlist');
                    $('ul.adminformlist > li > label:first-child, ul.adminformlist > li > span:first-child, dl.adminformlist > dd > label:first-child, dl.adminformlist > dd > span:first-child').addClass('ie_adminformlist_child');
                }
                // IE6 / IE7
                if (!document.querySelector) {
                    $('button').addClass('ie_button');
                }
            }

            // Profiles list
            // buttons
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

            // Table striping
            $('div#jce tbody tr:odd').addClass('odd');

            // HTML5 style form elements
            this._formWidgets();
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
            var title 	= o.title || '';

            var iframe 	= document.createElement('iframe');

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
            
            // add idd if set
            if (o.id) {
            	$(div).attr('id', o.id);
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
            var id = el.id, span = document.createElement('span');

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
        }
    };    
})(jQuery);
// global shortcut
var $jce = jQuery.jce;