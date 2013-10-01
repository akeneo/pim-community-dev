/* global define */
define(['jquery'],
function($) {
    'use strict';
    $.ajaxSetup({
        headers: {
            'X-CSRF-Header': 1
        }
    });
    $.expr[':'].parents = function(a, i, m){
        return $(a).parents(m[3]).length < 1;
    };
    return $;
});