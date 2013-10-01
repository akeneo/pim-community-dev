/* jshint browser:true */
/* global require */
require(['jquery', 'oro/dialog-widget'],
function($, DialogWidget) {
    'use strict';
    $(function () {
        $(document).on('click', '.view-email-entity-btn', function (e) {
            new DialogWidget({
                url: $(this).attr('href'),
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
