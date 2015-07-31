'use strict';

define(function (require) {
    var $ = require('jquery');
    var _ = require('underscore');
    var TemplateController = require('pim/controller/template');
    var router = require('pim/router');
    require('jquery.form');

    return TemplateController.extend({
        events: {
            'submit form': 'submitForm'
        },
        submitForm: function (event) {
            var $form = $(event.currentTarget);

            router.showLoadingMask();

            $form.ajaxSubmit({
                complete: _.bind(function (xhr) {
                    this.renderTemplate(xhr.responseText);
                    router.hideLoadingMask();
                }, this)
            });

            return false;
        }
    });
});
