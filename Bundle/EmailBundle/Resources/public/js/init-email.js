/* jshint browser:true */
/* global require */
require(['jquery', 'underscore', 'oro/dialog-widget'],
function($, _, DialogWidget) {
    'use strict';
    $(function () {
        $(document).on('click', '.view-email-button', function (e) {
            var url = $(this).attr('data-url');
            if (_.isUndefined(url)) {
                url = $(this).attr('href');
            }
            new DialogWidget({
                url: url,
                dialogOptions: {
                    allowMaximize: true,
                    allowMinimize: true,
                    dblclick: 'maximize',
                    maximizedHeightDecreaseBy: 'minimize-bar',
                    width: 1000,
                    title: $(this).attr('title')
                }
            }).render();
            e.preventDefault();
        });
    });
});
