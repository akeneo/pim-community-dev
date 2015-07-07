'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/attribute-manager',
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
        AttributeManager,
        messenger,
        submitTemplate,
        modifiedByDraftTemplate
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            submitTemplate: _.template(submitTemplate),
            modifiedByDraftTemplate: _.template(modifiedByDraftTemplate),
            productId: null,
            isOutdated: true,
            events: {
                'click .submit-draft': 'submitDraft',
                'click .modified-by-draft': 'showWorkingCopy'
            },

            /**
             * Configure this extension
             *
             * @returns {Promise}
             */
            configure: function () {
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:pre_save', this.onProductPreSave);

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
                event.promises.push(this.applyDraft(event.product));
            },

            /**
             * Event callback called just before data is sent to backend to be saved
             */
            onProductPreSave: function() {
                this.clearDraftCache();
            },

            /**
             * Mark a field as "modified by draft" if necessary
             *
             * @param {Object} event
             * @returns {Object}
             */
            addFieldExtension: function (event) {
                var field = event.field;

                event.promises.push(
                    this.isValueChanged(field)
                        .then(_.bind(function (isValueChanged) {
                            if (isValueChanged) {
                                var $element = this.modifiedByDraftTemplate();
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
                    .fetchForProduct(this.productId)
                    .then(_.bind(function (draft) {
                        // TODO: use a better way to trigger the rendering (e.g. an event on fetch)
                        if (this.isOutdated) {
                            this.render();
                        }

                        return draft;
                    }, this));
            },

            /**
             * Clear draft fetcher's cache
             */
            clearDraftCache: function() {
                this.isOutdated = true;
                FetcherRegistry.getFetcher('product-draft')
                    .clear(this.productId);
            },

            /**
             * Apply draft values on product values
             * productData is modified by reference
             *
             * @param {Object} productData
             * @returns {Promise}
             */
            applyDraft: function (productData) {
                return this.getDraft()
                    .then(_.bind(function (draft) {
                        var changes = draft.changes;
                        if (changes && changes.values) {
                            _.each(changes.values, function (draftValues, attributeCode) {
                                _.each(draftValues, function (draftValue) {
                                    var productValue = _.findWhere(
                                        productData.values[attributeCode],
                                        {locale: draftValue.locale, scope: draftValue.scope}
                                    );
                                    productValue.data = draftValue.data;
                                });
                            });
                        }
                    }, this));
            },

            /**
             * Check if the specified field's value has been modified in the current draft, in any locale or scope
             *
             * @param {Object} field
             * @returns {Boolean}
             */
            isValueChanged: function (field) {
                var attribute = field.attribute;

                return this.getDraft()
                    .then(function (draft) {
                        var changes = draft.changes;
                        if (!changes || !changes.values || !_.has(changes.values, attribute.code)) {
                            return false;
                        }

                        return !_.isUndefined(AttributeManager.getValue(
                            changes.values[attribute.code],
                            attribute,
                            field.context.locale,
                            field.context.scope
                        ));
                    });
            },

            /**
             * Refresh the "send for approval" button rendering
             *
             * @returns {Object}
             */
            render: function () {
                this.getDraft()
                    .then(_.bind(function (draft) {
                        if (!_.isUndefined(draft.status)) {
                            this.$el.html(
                                this.submitTemplate({
                                    'submitted': draft.status !== 0
                                })
                            );
                            this.delegateEvents();
                            this.$el.removeClass('hidden');
                        } else {
                            this.$el.addClass('hidden');
                        }

                        this.isOutdated = false;
                    }, this));

                return this;
            },

            /**
             * Submit the current draft to backend for approval
             *
             * @returns {Object}
             */
            submitDraft: function () {
                this.getDraft()
                    .then(function (draft) {
                        return FetcherRegistry.getFetcher('product-draft').sendForApproval(draft);
                    })
                    .then(function () {
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                        );
                    })
                    .fail(function () {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__('pimee_enrich.entity.product_draft.flash.draft_not_sendable')
                        );
                    });

                return this;
            },

            /**
             * Trigger an event to open the working copy panel
             */
            showWorkingCopy: function () {
                mediator.trigger('draft:action:show_working_copy');
            }
        });
    }
);
