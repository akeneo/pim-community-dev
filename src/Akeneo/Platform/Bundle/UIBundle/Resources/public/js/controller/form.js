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

            /**
             * Handle form submission on the page
             *
             * @param {Event} event
             *
             * @return {boolean}
             */
            submitForm: function (event) {
                var $form = $(event.currentTarget);

                router.showLoadingMask();

                $form.ajaxSubmit({
                    complete: (xhr) => {
                        this.afterSubmit(xhr, $form);
                    }
                });

                return false;
            },

            /**
             * Called after a successful submit (after a submitForm)
             *
             * @param {Object} xhr
             */
            afterSubmit: function (xhr) {
                if (!this.active) {
                    return;
                }

                if (xhr.responseJSON && xhr.responseJSON.route) {
                    router.redirectToRoute(
                        xhr.responseJSON.route,
                        xhr.responseJSON.params ? xhr.responseJSON.params : {},
                        {trigger: true}
                    );
                } else {
                    this.renderTemplate(xhr.responseText);
                    mediator.trigger('route_complete pim:reinit');
                    router.hideLoadingMask();
                }
            }
        });
    }
);
