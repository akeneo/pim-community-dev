'use strict';

define([
    'jquery',
    'underscore',
    'pim/template/category-tree/list',
    'pim/form'
], function (
    $,
    _,
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
