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
        'oro/mediator',
        'pim/form',
        'pim/fetcher-registry',
        'oro/messenger',
        'text!pimee/template/product/submit-draft',
        'text!pimee/template/product/tab/attribute/modified-by-draft'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        FetcherRegistry,
        messenger,
        submitTemplate,
        modifiedByDraftTemplate
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            submitTemplate: _.template(submitTemplate),
            modifiedByDraftTemplate: _.template(modifiedByDraftTemplate),
            confirmationMessage: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes'),
            confirmationTitle: _.__('pimee_enrich.entity.product_draft.confirmation.discard_changes_title'),
            productId: null,
            events: {
                'click .submit-draft': 'onSubmitDraft'
            },

            /**
             * Configure this extension
             *
             * @returns {Promise}
             */
            configure: function () {
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:pre_save', this.onProductPreSave);
                this.listenTo(mediator, 'product:action:post_update', this.onProductPostUpdate);

                this.stopListening(mediator, 'field:extension:add');
                this.listenTo(mediator, 'field:extension:add', this.addFieldExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },

            /**
             * Event callback called just after product is fetched form backend
             *
             * @param {Object} event
             */
            onProductPostFetch: function (event) {
                this.productId = event.product.meta.id;
                event.promises.push(
                    this.getDraft()
                        .then(function (draft) {
                            draft.applyChanges(event.product.values);
                        })
                );
            },

            /**
             * Remove draft from fetcher cache just before data is sent to backend to be saved
             */
            onProductPreSave: function() {
                this.clearDraftCache();
            },

            /**
             * Re-render extension after saving
             */
            onProductPostUpdate: function () {
                this.render();
            },

            /**
             * Mark a field as "modified by draft" if necessary
             *
             * @param {Object} event
             *
             * @returns {Object}
             */
            addFieldExtension: function (event) {
                var field = event.field;

                event.promises.push(
                    this.getDraft()
                        .then(_.bind(function (draft) {
                            if (draft.isValueChanged(field)) {
                                var $element = $(this.modifiedByDraftTemplate());
                                $element.on('click', this.showWorkingCopy);

                                field.addElement('label', 'modified_by_draft', $element);
                            }
                        }, this))
                );

                return this;
            },

            /**
             * Retrieve the current draft using the draft fetcher
             *
             * @returns {Promise}
             */
            getDraft: function () {
                return FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(this.productId);
            },

            /**
             * Clear draft fetcher's cache
             */
            clearDraftCache: function() {
                FetcherRegistry.getFetcher('product-draft')
                    .clear(this.productId);
            },

            /**
             * Refresh the "send for approval" button rendering
             *
             * @returns {Object}
             */
            render: function () {
                this.getDraft()
                    .then(_.bind(function (draft) {
                        if (draft.hasStatus()) {
                            this.$el.html(
                                this.submitTemplate({
                                    'submitted': draft.isReady()
                                })
                            );
                            this.delegateEvents();
                            this.$el.removeClass('hidden');
                        } else {
                            this.$el.addClass('hidden');
                        }
                    }, this));

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
                this.getDraft()
                    .then(function (draft) {
                        return draft.sendForApproval();
                    })
                    .then(_.bind(function () {
                        this.clearDraftCache();
                        this.refreshModel();

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
            },

            /**
             * Trigger an event to open the working copy panel
             */
            showWorkingCopy: function () {
                mediator.trigger('draft:action:show_working_copy');
            },

            /**
             * Update the form model with draft changes
             */
            refreshModel: function () {
                this.getDraft()
                    .then(_.bind(function (draft) {
                        var formValues = this.getFormModel().get('values');
                        draft.applyChanges(formValues);
                        this.getFormModel().set('values', formValues);
                        mediator.trigger('product:action:post_update');
                    }, this));
            }
        });
    }
);
