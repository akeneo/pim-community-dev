/* jshint browser:true */
/* global require */
require(['jquery'],
function($) {
    'use strict';

    $(function() {
        $(window)
            .resize(function() {
                var form = $('form.form-signin'),
                    thisHeight = $(window).height()/2 - form.height()/2;
                if (thisHeight > 40) {
                    thisHeight = thisHeight -40;
                }
                form.css('margin-top', thisHeight );
            })
            .trigger('resize');

        var hashUrl = window.location.hash,
            hashUrlTag = '#url=',
            hashArray;
        if (hashUrl.length && hashUrl.match(hashUrlTag)) {
            if (hashUrl.indexOf('|') !== -1) {
                hashUrl = hashUrl.substring(0, hashUrl.indexOf('|'));
            }
            hashUrl = hashUrl.replace(hashUrlTag, '');
            hashArray = hashUrl.split('php');
            if (hashArray[1]) {
                hashUrl = hashArray[1];
            }
            $('input[name="_target_path"]').val(hashUrl);
        }
    });
});