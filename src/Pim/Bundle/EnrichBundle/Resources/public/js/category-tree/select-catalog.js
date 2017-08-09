'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'pim/template/category-tree/select-catalog',
    'pim/form'
], function (
    $,
    _,
    Backbone,
    template,
    BaseForm
) {

    return BaseForm.extend({
        template: _.template(template),
        render() {
            this.$el.html(this.template({}));
        }
    });
});
