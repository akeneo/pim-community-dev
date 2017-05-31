'use strict';

define(
    [
        'pim/controller/group'
    ],
    function (BaseController) {
        return BaseController.extend({
            initialize: function () {
                this.config = __moduleConfig;
            }
        });
    }
);
