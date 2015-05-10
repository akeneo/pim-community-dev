'use strict';

define(function (require) {
    var BaseController = require('pim/controller/base');
    var $ = require('jquery');

    return BaseController.extend({
        renderRoute: function (route, path) {
            return $.get(path).then(function (template) {
                $('#container').html(template);
            }).promise();
        }
    });
});
