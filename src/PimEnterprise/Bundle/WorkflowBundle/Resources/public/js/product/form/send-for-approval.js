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
        'routing',
        'oro/messenger',
        'oro/translator',
        'pim/form',
        'pimee/template/product/submit-draft',
        'pim/form-modal'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        messenger,
        __,
        BaseForm,
        submitTemplate,
        FormModal
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            submitTemplate: _.template(submitTemplate),
            confirmationMessage: __('pimee_enrich.entity.product_draft.confirmation.discard_changes'),
            confirmationTitle: __('pimee_enrich.entity.product_draft.confirmation.discard_changes_title'),
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
                this.routes = __moduleConfig.routes;

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
                var callback = function () {
                    var deferred = $.Deferred();

                    deferred.resolve();

                    return deferred;
                };
                var myFormModal = new FormModal(
                    'pimee-workflow-send-for-approval-comment',
                    callback,
                    {
                        title: __('pimee_enrich.entity.product_draft.modal.send_for_approval'),
                        cancelText: __('pimee_enrich.entity.product_draft.modal.cancel'),
                        okText: __('pimee_enrich.entity.product_draft.modal.confirm')
                    }
                );

                myFormModal
                    .open()
                    .then(function (myFormData) {
                        var comment = _.isUndefined(myFormData.comment) ? null : myFormData.comment;

                        this.getRoot().trigger('pim_enrich:form:state:confirm', {
                            message: this.confirmationMessage,
                            title:   this.confirmationTitle,
                            action:  this.submitDraft.bind(this, comment)
                        });
                    }.bind(this));
                myFormModal.modal.$el.addClass('modal--fullPage');
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
                .then(function (product) {
                    this.setData(product);

                    this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);

                    messenger.notify(
                        'success',
                        __('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                    );
                }.bind(this))
                .fail(function () {
                    messenger.notify(
                        'error',
                        __('pimee_enrich.entity.product_draft.flash.draft_not_sendable')
                    );
                });
            }
        });
    }
);
