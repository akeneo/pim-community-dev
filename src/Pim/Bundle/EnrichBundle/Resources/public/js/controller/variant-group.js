'use strict';

define(
    [
        'pim/controller/group',
        'config'
    ],
    function (BaseController, module) {
        return BaseController.extend({
            initialize: function () {
                this.config = module.config();
            }
        });
    }
);
