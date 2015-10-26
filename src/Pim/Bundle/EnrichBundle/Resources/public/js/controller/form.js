'use strict';

define(function (require) {
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oro/mediator');
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
                    mediator.trigger('route_complete pim:reinit');
                    router.hideLoadingMask();
                }, this)
            });

            return false;
        }
    });
});
