'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'pim/form'
], function (
    $,
    _,
    Backbone,
    BaseForm
) {
    return BaseForm.extend({
        configure() {
            console.log('configure parent category-tree');

            return BaseForm.prototype.configure.apply(this, arguments);
        }
    });
});
