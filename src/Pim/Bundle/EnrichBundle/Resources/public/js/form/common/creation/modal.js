'use strict';

define(
    [
        'jquery',
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
            initialize(meta) {
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
            open() {
                const deferred = $.Deferred();

                const modal = new Backbone.BootstrapModal({
                    title: __(this.config.labels.title),
                    content: '',
                    cancelText: __('pim_enrich.entity.create_popin.labels.cancel'),
                    okText: __('pim_enrich.entity.create_popin.labels.save'),
                    okCloses: false
                });

                modal.open();

                const modalBody = modal.$('.modal-body');
                modalBody.addClass('creation');

                this.render()
                    .setElement(modalBody)
                    .render();

                modal.on('cancel', () => {
                    deferred.reject();
                    modal.remove();
                });

                modal.on('ok', this.confirmModal.bind(this, modal, deferred));

                return deferred.promise();
            },

            /**
             * Confirm the modal and redirect to route after save
             * @param  {Object} modal    The backbone view for the modal
             * @param  {Promise} deferred Promise to resolve
             */
            confirmModal(modal, deferred) {
                this.save().done(entity => {
                    modal.close();
                    modal.remove();
                    deferred.resolve();

                    let routerParams = {};

                    if (this.config.routerKey) {
                        routerParams[this.config.routerKey] = entity[this.config.routerKey];
                    } else {
                        routerParams = {id: entity.meta.id};
                    }

                    messenger.notify('success', __(this.config.successMessage));

                    router.redirectToRoute(
                      this.config.editRoute,
                      routerParams
                  );
                });
            },

            /**
             * Normalize the path property for validation errors
             * @param  {Array} errors
             * @return {Array}
             */
            normalize(errors) {
                const values = errors.values || [];

                return values.map(error => {
                    if (!error.path) {
                        error.path = error.attribute;
                    }

                    return error;
                })
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save() {
                this.validationErrors = {};

                const loadingMask = new LoadingMask();
                this.$el.empty().append(loadingMask.render().$el.show());

                const data = this.getFormData();

                return $.ajax({
                    url: Routing.generate(this.config.postUrl),
                    type: 'POST',
                    data: JSON.stringify(data)
                }).fail(function (response) {
                    const errors = response.responseJSON ?
                        this.normalize(response.responseJSON) : [{message: __('error.common')}];
                    this.validationErrors = errors;
                    this.render();
                }.bind(this))
                .always(() => loadingMask.remove());
            }
        });
    }
);
