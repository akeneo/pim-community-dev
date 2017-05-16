'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/form',
        'pim/form-builder',
        'pim/user-context',
        'oro/translator',
        'oro/loading-mask',
        'pim/router'
    ],
    function ($, _, Backbone, Routing, BaseForm, FormBuilder, UserContext, __, LoadingMask, router) {
        return BaseForm.extend({
            config: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Opens the modal then instantiates the creation form inside it.
             * This function returns a rejected promise when the popin
             * is canceled and a resolved one when it's validated.
             *
             * @return {Promise}
             */
            open: function () {
                var deferred = $.Deferred();

                var modal = new Backbone.BootstrapModal({
                    title: __(this.config.labels.title),
                    content: '',
                    cancelText: __('pim_enrich.entity.create_popin.labels.cancel'),
                    okText: __('pim_enrich.entity.create_popin.labels.save'),
                    okCloses: false
                });

                modal.open();

                var modalBody = modal.$('.modal-body');
                modalBody.css('min-height', 150);
                modalBody.css('overflow-y', 'hidden');

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(modalBody).show();

                var self = this;
                FormBuilder.build(this.config.innerForm)
                    .then(function (form) {
                        form.setElement(modalBody)
                            .render();

                        modal.on('cancel', function () {
                            deferred.reject();
                            modal.remove();
                        });

                        modal.on('ok', function () {
                            form.save()
                                .done(function (entity) {
                                    modal.close();
                                    modal.remove();
                                    deferred.resolve();

                                    var routerParams = {};
                                    if (self.config.routerKey) {
                                        routerParams[self.config.routerKey] = entity[self.config.routerKey];
                                    } else {
                                        routerParams = {id: entity.meta.id};
                                    }

                                    router.redirectToRoute(
                                        self.config.editRoute,
                                        routerParams
                                    );
                                });
                        });
                    });

                return deferred.promise();
            }
        });
    }
);
