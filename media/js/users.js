/**
 * @version		$Id: users.js 203 2011-06-01 19:02:19Z happy_noodle_boy $
 * @package   	JCE
 * @copyright 	Copyright © 2009-2011 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function($) {
    $.jce.Users = {
        select : function() {
            var self = this, u = [], v, s, o, h;
            s = window.parent.document.getElementById('users').options;

            $('input:checkbox:checked').each( function() {
                v = $(this).val();

                if (u = document.getElementById('username_' + v)) {
                    h = $.trim(u.innerHTML);

                    if ($.jce.Users.check(s, v)) {
                        return;
                    }

                    o = new Option(h, v);
                    s[s.length] = o;
                }
            });

        },

        check : function(s, v) {
            var a = [];
            $.each(s, function(i, n) {
                a.push(n.value);
            });

            return a.indexOf(v) != -1;
        }

    };
    
    window.selectUsers = $.jce.Users.select;
    
})(jQuery);