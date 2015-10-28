'use strict';

define([
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/controller/template',
        'pim/router',
        'jquery.form'
    ], function (
        $,
        _,
        mediator,
        TemplateController,
        router
    ) {
        return TemplateController.extend({
            events: {
                'submit form': 'submitForm'
            },
            submitForm: function (event) {
                var $form = $(event.currentTarget);

                router.showLoadingMask();

                $form.ajaxSubmit({
                    complete: function (xhr) {
                        this.afterSubmit(xhr, $form);
                    }.bind(this)
                });

                return false;
            },
            afterSubmit: function (xhr) {
                this.renderTemplate(xhr.responseText);
                mediator.trigger('route_complete pim:reinit');
                router.hideLoadingMask();
            }
        });
    }
);
