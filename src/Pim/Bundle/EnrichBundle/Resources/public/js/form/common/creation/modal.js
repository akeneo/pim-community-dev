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
        'pim/router',
        'oro/messenger'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        BaseForm,
        FormBuilder,
        UserContext,
        __,
        LoadingMask,
        router,
        messenger
    ) {
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
                modalBody.addClass('creation');

                this.render()
                    .setElement(modalBody)
                    .render();

                modal.on('cancel', function () {
                    deferred.reject();
                    modal.remove();
                });

                modal.on('ok', function () {
                    this.save()
                        .done(function (entity) {
                            modal.close();
                            modal.remove();
                            deferred.resolve();

                            var routerParams = {};
                            if (this.config.routerKey) {
                                routerParams[this.config.routerKey] = entity[this.config.routerKey];
                            } else {
                                routerParams = {id: entity.meta.id};
                            }

                            messenger.notificationFlashMessage('success', __(this.config.successMessage));

                            router.redirectToRoute(
                                this.config.editRoute,
                                routerParams
                            );
                        }.bind(this));
                }.bind(this));

                return deferred.promise();
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save: function () {
                this.validationErrors = {};

                var loadingMask = new LoadingMask();
                this.$el.empty().append(loadingMask.render().$el.show());

                return $.post(Routing.generate(this.config.postUrl), JSON.stringify(this.getFormData()))
                    .fail(function (response) {
                        this.validationErrors = response.responseJSON ?
                            response.responseJSON.values : [{message: __('error.common')}];
                        this.render();
                    }.bind(this))
                    .always(function () {
                        loadingMask.remove();
                    });
            }
        });
    }
);
