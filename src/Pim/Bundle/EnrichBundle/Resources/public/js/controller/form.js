'use strict';

define(function (require) {
    var $ = require('jquery');
    var _ = require('underscore');
    var TemplateController = require('pim/controller/template');
    var router = require('pim/router');

    return TemplateController.extend({
        events: {
            'submit form': 'submitForm'
        },
        submitForm: function (event) {
            var $form = $(event.currentTarget);

            router.showLoadingMask();
            $.ajax({
                type: $form.prop('method'),
                url:  $form.prop('action'),
                data: $form.serialize()
            }).always(_.bind(function (template) {
                this.$el.html(template);
                router.hideLoadingMask();
            }, this));

            return false;
        }
    });
});
