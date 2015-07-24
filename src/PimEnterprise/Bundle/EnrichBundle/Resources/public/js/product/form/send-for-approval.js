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
        'oro/mediator',
        'oro/messenger',
        'pim/form',
        'pim/product-manager',
        'text!pimee/template/product/submit-draft'
    ],
    function (
        $,
        _,
        Backbone,
        module,
        mediator,
        messenger,
        BaseForm,
        ProductManager,
        submitTemplate
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

                this.listenTo(mediator, 'product:action:post_update', this.onProductPostUpdate);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Re-render extension after saving
             */
            onProductPostUpdate: function () {
                this.render();
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
                mediator.trigger('pim_enrich:form:state:confirm', {
                    message: this.confirmationMessage,
                    title: this.confirmationTitle,
                    action: _.bind(this.submitDraft, this)
                });
            },

            /**
             * Submit the current draft to backend for approval
             */
            submitDraft: function () {
                $.post(
                    Routing.generate(
                        this.routes.ready,
                        {productId: this.getProductId()}
                    )
                )
                .done(_.bind(ProductManager.generateMissing, this))
                .then(_.bind(function (product) {
                    this.setData(product);

                    messenger.notificationFlashMessage(
                        'success',
                        _.__('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                    );
                }, this))
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
