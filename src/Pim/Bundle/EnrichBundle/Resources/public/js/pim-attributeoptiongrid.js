define(
    ['jquery'],
    function ($) {
        'use strict';

        var _init = function(gridId) {
            var $grid = $(gridId);

            console.log($grid);
        };

        return {
            init: _init
        };
    }
);
