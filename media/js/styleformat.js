(function($) {
    var previewStyles = ['fontFamily', 'fontSize', 'fontWeight', 'textDecoration', 'textTransform', 'color', 'backgroundColor'];

    // camelcase style - from JQuery 1.10.2 - http://code.jquery.com/jquery-1.10.2.js
    function camelCase(str) {
        return str.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function(all, letter) {
            return letter.toUpperCase();
        });
    }

    $(document).ready(function() {
        var init = true;

        $('div.styleformat-list').on('update', function() {
            var list = [], v = "";

            // get each styleformat item
            $('div.styleformat', this).each(function() {
                var data = {}, v, p = this;

                // only proceed if title and element set
                if ($('div.styleformat-item-title input', p).val() && $('div.styleformat-item-element select', p).val()) {
                    // get all values in sequence and encode
                    $('input[type="text"], select', p).each(function() {
                        var k = $(this).data('key'), v = $(this).val();

                        if (v !== "") {
                            data[k] = v;
                        }
                    });
                }

                // check if empty, convert to string
                if (!$.isEmptyObject(data)) {
                    list.push(data);
                }
            });

            if (list.length) {
                v = JSON.stringify(list);
            }
            
            if (!init) {
                // serialize and return
                $('input[type="hidden"]', this).val(v).change();
            }
        });

        /**
         * Update title styles
         * @param {type} n
         * @param {type} string
         * @returns {undefined}
         */
        function updateStyles(n, string) {
            $.each(string.split(';'), function(i, s) {
                var kv = $.trim(s).split(':');

                if (kv.length > 1) {
                    var k = $.trim(kv[0]), v = $.trim(kv[1]);
                    
                    if ($.inArray(camelCase(k), previewStyles) !== -1) {
                        $(n).css(k, v);
                    }
                }
            });
        }

        // trigger input change
        $('input[type="text"], select', $('div.styleformat')).change(function() {
            $('div.styleformat-list').trigger('update');

            var title = $('div.styleformat-item-title input', $(this).parents('div.styleformat')), v = $(this).val();

            if ($(this).data('key') === "element") {
                $(title).attr('class', "");

                if (/^(h[1-6]|em|strong|code|sub|sup)$/.test(v)) {
                    $(title).addClass(v);
                }
            }

            if ($(this).data('key') === "styles") {
                $(title).attr('style', "");

                updateStyles(title, v);
            }
        }).change();

        // create collapsible action
        $('a.close.collapse', 'div.styleformat').on('click.collapse', function(e) {
            $(this).siblings().not('.styleformat-item-title, .close').toggleClass('hide');
            $(this).toggleClass('icon-chevron-up icon-chevron-down');
        });

        // create close action
        $('div.styleformat a.close', 'div.styleformat-list').not('.plus, .handle, .collapse').click(function(e) {
            // if there  is only one item, clear and hide
            if ($('div.styleformat-list div.styleformat').length === 1) {
                $(this).parent().hide();
                // otherwise remove it
            } else {
                $(this).parent().remove();
            }
            $('div.styleformat-list').trigger('update');

            e.preventDefault();
        });

        // create new action
        $('a.close.plus', 'div.styleformat-list').click(function(e) {
            var $item = $(this).prev().clone(true).insertBefore(this).show();

            // show all
            $('div', $item).removeClass('hide');

            // trigger collapse
            $('a.close.collapse', $item).removeClass('icon-chevron-down').addClass('icon-chevron-up');
            
            // clear inputs
            $('input, select', $item).val("").first().focus();

            e.preventDefault();
        });

        // make sortable
        $('div.styleformat-list').sortable({
            axis: 'y',
            update: function(event, ui) {
                $('div.styleformat-list').trigger('update');
            },
            handle: 'a.handle',
            items: 'div.styleformat',
            placeholder: "styleformat-highlight",
            start: function(event, ui) {
                $(ui.placeholder).height($(ui.item).height());
            }
        });
        
        // set chevron
        //$('a.close.collapse', 'div.styleformat-list').removeClass('icon-chevron-up').addClass('icon-chevron-down');

        // hide all
        if ($('div.styleformat', 'div.styleformat-list').length > 1) {
            $('div.styleformat div', 'div.styleformat-list').not('div.styleformat-item-title').addClass('hide');
        }
        // set init flag false
        init = false;
    });
})(jQuery);