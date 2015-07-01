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
                'click .submit-draft': 'submitDraft'
            },
            configure: function () {
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:pre_save', this.onProductPreSave);

                this.stopListening(mediator, 'field:extension:add');
                this.listenTo(mediator, 'field:extension:add', this.addExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            onProductPostFetch: function (event) {
                this.productId = event.product.meta.id;
                event.promises.push(this.loadProductDraft(event.product));
            },
            onProductPreSave: function() {
                this.clearDraft();
            },
            addExtension: function (event) {
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
            getDraft: function () {
                return FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(this.productId)
                    .then(_.bind(function (draft) {
                        if (this.isOutdated) {
                            this.render();
                        }

                        return draft;
                    }, this));
            },
            clearDraft: function() {
                this.isOutdated = true;
                return FetcherRegistry.getFetcher('product-draft')
                    .clear(this.productId);
            },
            loadProductDraft: function (productData) {
                return this.getDraft()
                    .then(_.bind(function (draft) {
                        var changes = draft.changes;
                        if (changes && changes.values) {
                            productData.values = _.extend(
                                productData.values || {},
                                changes.values
                            )
                        }
                    }, this));
            },
            isValueChanged: function (field) {
                var attribute = field.attribute;

                return this.getDraft()
                    .then(function (draft) {
                        var changes = draft.changes;
                        if (!changes || !changes.values || !_.has(changes.values, attribute.code)) {
                            return false;
                        }

                        return undefined !== AttributeManager.getValue(
                            changes.values[attribute.code],
                            attribute,
                            field.context.locale,
                            field.context.scope
                        );
                    });
            },
            render: function () {
                this.getDraft()
                    .then(_.bind(function (draft) {
                        if (undefined !== draft.status) {
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
            submitDraft: function () {
                this.getDraft()
                    .then(function (draft) {
                        return FetcherRegistry.getFetcher('product-draft').sendForApproval(draft)
                    })
                    .then(function () {
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                        );
                    });

                return this;
            }
        });
    }
);
