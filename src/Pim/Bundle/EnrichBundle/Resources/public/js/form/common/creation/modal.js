'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'pim/form',
        'pim/form-builder',
        'pim/user-context',
        'oro/loading-mask',
        'pim/router',
        'oro/messenger',
        'pim/template/form/creation/modal'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        BaseForm,
        FormBuilder,
        UserContext,
        LoadingMask,
        router,
        messenger,
        template
    ) {
        return BaseForm.extend({
            config: {},
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize(meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    titleLabel: __(this.config.labels.title),
                    subTitleLabel: __(this.config.labels.subTitle),
                    contentLabel: __(this.config.labels.content),
                    picture: this.config.picture,
                    fields: null
                }));

                this.renderExtensions();

                return this;
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
                modal.$el.addClass('modal--fullPage');

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

                let data = $.extend(this.getFormData(),
                this.config.defaultValues || {});

                if (this.config.excludedProperties) {
                    data = _.omit(data, this.config.excludedProperties)
                }

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
