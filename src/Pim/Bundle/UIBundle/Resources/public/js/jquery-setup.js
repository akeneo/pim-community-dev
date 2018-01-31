define(['jquery'],
function ($) {
    'use strict';
    $.ajaxSetup({
        headers: {
            'X-CSRF-Header': 1
        }
    });
    $.expr[':'].parents = function (a, i, m) {
        return $(a).parents(m[3]).length < 1;
    };
    // used to indicate app's activity, such as AJAX request or redirection, etc.
    $.isActive = $.proxy(function (flag) {
        if ($.type(flag) !== 'undefined') {
            this.active = flag;
        }

        return $.active || this.active;
    }, {active: false});

    return $;
});
