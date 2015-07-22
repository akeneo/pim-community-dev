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
        submitTemplate
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            submitTemplate: _.template(submitTemplate),
            confirmationMessage: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes'),
            confirmationTitle: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes_title'),
            routes: {},
            productId: null,
            draftStatus: null,
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

                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:post_update', this.onProductPostUpdate);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Event callback called just after product is fetched form backend
             *
             * @param {Object} product
             */
            onProductPostFetch: function (product) {
                this.productId = product.meta.id;
                this.draftStatus = product.meta.draft_status;
            },

            /**
             * Re-render extension after saving
             */
            onProductPostUpdate: function () {
                this.render();
            },

            /**
             * Refresh the "send for approval" button rendering
             *
             * @returns {Object}
             */
            render: function () {
                if (null !== this.draftStatus) {
                    this.$el.html(
                        this.submitTemplate({
                            'submitted': 0 !== this.draftStatus
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
                        {productId: this.productId}
                    )
                )
                .then(_.bind(function () {
                    mediator.trigger('product:action:post_update');

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
