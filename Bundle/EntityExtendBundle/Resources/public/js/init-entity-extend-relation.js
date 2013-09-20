/* jshint browser:true */
/* global require */
require(['jquery', 'underscore'],
function($, _) {
    'use strict';
    $(function() {
        $(document).on('change', 'form select.extend-rel-target-name', function (e) {
            var el     = $(this),
                target = el.find('option:selected').attr('value');

            console.log ( target );


            return false;
        });
    });
});
