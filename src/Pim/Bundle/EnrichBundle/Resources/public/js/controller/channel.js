'use strict';

define(function (require) {
    var _ = require('underscore');
    var FormController = require('pim/controller/form');
    require('jquery.select2');

    return FormController.extend({
        renderRoute: function (route, path) {
            return FormController.prototype.renderRoute.apply(this, arguments).then(_.bind(function () {
                this.$('select').select2();
            }, this));
        }
    });
});
