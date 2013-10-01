/* jshint browser:true */
/* global require */
require(['jquery'],
function($) {
    'use strict';

    $(function() {
        setTimeout(function() {
            // emulates 'document ready state' for selenium tests
            document['page-rendered'] = true;
        }, 100);
    });
});