require(['jquery'],
function($) {
    'use strict';

    $(function() {

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
