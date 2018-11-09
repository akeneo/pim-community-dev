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
            modal: null,
            deferred: null,
            events: {
                'click .save': 'save',
                'click .ok': 'confirmModal',
            },

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
                    saveLabel: __('pim_common.save'),
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
                this.deferred = $.Deferred();

                this.modal = new Backbone.BootstrapModal({
                    content: '',
                    okCloses: false
                });

                this.modal.open();
                this.modal.$el.addClass('modal--fullPage');
                this.modal.$el.find('.modal-footer .ok').remove();

                const modalBody = this.modal.$('.modal-body');
                modalBody.addClass('creation');

                this.setElement(modalBody)
                    .render();

                this.modal.on('cancel', () => {
                    this.deferred.reject();
                    this.modal.remove();
                });

                return this.deferred.promise();
            },

            /**
             * Confirm the modal and redirect to route after save
             */
            confirmModal() {
                this.save().done(entity => {
                    this.modal.close();
                    this.modal.remove();
                    this.deferred.resolve();

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
                    if (response.responseJSON) {
                        this.getRoot().trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON.values}
                        );
                    }

                    this.validationErrors = response.responseJSON ?
                        this.normalize(response.responseJSON) : [{
                            message: __('pim_enrich.entity.fallback.generic_error')
                        }];
                    this.render();
                }.bind(this))
                .always(() => loadingMask.remove());
            }
        });
    }
);
