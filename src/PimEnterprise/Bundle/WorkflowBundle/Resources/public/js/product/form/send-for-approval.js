'use strict';
/**
 * Draft extension
 *
 * @author Filips Alpe <filips@akeneo.com>
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'module',
        'routing',
        'oro/messenger',
        'pim/form',
        'pim/product-manager',
        'text!pimee/template/product/submit-draft',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        Backbone,
        module,
        Routing,
        messenger,
        BaseForm,
        ProductManager,
        submitTemplate,
        FormBuilder
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            submitTemplate: _.template(submitTemplate),
            confirmationMessage: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes'),
            confirmationTitle: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes_title'),
            routes: {},
            events: {
                'click .submit-draft': 'onSubmitDraft'
            },

            /**
             * Configure this extension
             *
             * @returns {Promise}
             */
            configure: function () {
                this.routes = module.config().routes;

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Return the current productId
             *
             * @return {number}
             */
            getProductId: function () {
                return this.getFormData().meta.id;
            },

            /**
             * Return the current draft status
             * Return null if there is no draft (not created yet or the user owns the product)
             *
             * @returns {number|null}
             */
            getDraftStatus: function () {
                return this.getFormData().meta.draft_status;
            },

            /**
             * Refresh the "send for approval" button rendering
             *
             * @returns {Object}
             */
            render: function () {
                if (null !== this.getDraftStatus()) {
                    this.$el.html(
                        this.submitTemplate({
                            'submitted': 0 !== this.getDraftStatus()
                        })
                    );
                    this.delegateEvents();
                    this.$el.removeClass('hidden');
                } else {
                    this.$el.addClass('hidden');
                }

                return this;
            },

            /**
             * Callback triggered on "send for approval" button click
             */
            onSubmitDraft: function () {
                var deferred = $.Deferred();

                FormBuilder.build('pim-notification-comment').done(function (form) {
                    var modal = new Backbone.BootstrapModal({
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        title: _.__('pimee_enrich.entity.product_draft.modal.send_for_approval'),
                        content: '',
                        cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel'),
                        okText: _.__('pimee_enrich.entity.product_draft.modal.confirm')
                    });

                    modal.open();
                    form.setElement(modal.$('.modal-body')).render(
                        {'title': _.__('pimee_enrich.entity.product_draft.modal.title')}
                    );
                    modal.on('cancel', deferred.reject);
                    modal.on('ok', function () {
                        this.getRoot().trigger('pim_enrich:form:state:confirm', {
                            message: this.confirmationMessage,
                            title:   this.confirmationTitle,
                            action:  this.submitDraft.bind(this, $('.modal-body textarea').val())
                        });

                        modal.close();
                    }.bind(this));
                }.bind(this));
            },

            /**
             * Submit the current draft to backend for approval
             */
            submitDraft: function (comment) {
                $.post(
                    Routing.generate(
                        this.routes.ready,
                        {productId: this.getProductId(), comment: comment}
                    )
                )
                .then(ProductManager.generateMissing.bind(ProductManager))
                .then(function (product) {
                    this.setData(product);

                    this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);

                    messenger.notificationFlashMessage(
                        'success',
                        _.__('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                    );
                }.bind(this))
                .fail(function () {
                    messenger.notificationFlashMessage(
                        'error',
                        _.__('pimee_enrich.entity.product_draft.flash.draft_not_sendable')
                    );
                });
            }
        });
    }
);
