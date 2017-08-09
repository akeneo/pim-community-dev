'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'pim/template/category-tree/switch',
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

        /**
         * @inheritDoc
         */
        render() {
            this.$el.html(this.template({}));
        }
    });
});
