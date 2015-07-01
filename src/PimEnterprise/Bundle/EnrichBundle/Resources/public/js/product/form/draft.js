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
            draft: undefined,
            events: {
                'click .submit-draft': 'submitDraft'
            },
            initialize: function () {
                this.draft = new Backbone.Model();

                this.listenTo(this.draft, 'change', this.render);
            },
            configure: function () {
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:post_update', this.reloadProductDraft);

                this.stopListening(mediator, 'field:extension:add');
                this.listenTo(mediator, 'field:extension:add', this.addExtension);

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addExtension: function (event) {
                var field = event.field;
                if (this.isValueChanged(field)) {
                    var $element = this.modifiedByDraftTemplate();
                    field.addElement('label', 'modified_by_draft', $element);
                }

                return this;
            },
            onProductPostFetch: function (event) {
                event.promises.push(this.loadProductDraft(event.product));
            },
            loadProductDraft: function (productData) {
                return FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(productData.meta.id)
                    .then(_.bind(function (daftData) {
                        this.updateProductDraft(daftData);
                        var changes = this.draft.get('changes');

                        if (changes && changes.values) {
                            productData.values = _.extend(
                                productData.values || {},
                                changes.values
                            )
                        }
                    }, this));
            },
            reloadProductDraft: function (productData) {
                var fetcher = FetcherRegistry.getFetcher('product-draft');
                fetcher.clear(productData.meta.id);
                fetcher
                    .fetchForProduct(productData.meta.id)
                    .then(_.bind(function (daftData) {
                        this.updateProductDraft(daftData);
                    }, this));
            },
            updateProductDraft: function (daftData) {
                this.draft.set(daftData);
            },
            isValueChanged: function (field) {
                var attribute = field.attribute;

                var changes = this.draft.get('changes');
                if (!changes || !changes.values || !_.has(changes.values, attribute.code)) {
                    return false;
                }

                return undefined !== AttributeManager.getValue(
                    changes.values[attribute.code],
                    attribute,
                    field.context.locale,
                    field.context.scope
                );
            },
            render: function () {
                if (undefined !== this.draft.get('status')) {
                    this.$el.html(
                        this.submitTemplate({
                            'submitted': this.draft.get('status') !== 0
                        })
                    );
                    this.delegateEvents();
                    this.$el.removeClass('hidden');
                } else {
                    this.$el.addClass('hidden');
                }

                return this;
            },
            submitDraft: function () {
                FetcherRegistry.getFetcher('product-draft').sendForApproval(this.draft.toJSON()).done(
                    _.bind(function (data) {
                        this.updateProductDraft(data);

                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_enrich.entity.product_draft.flash.sent_for_approval')
                        );
                    }, this)
                );
            }
        });
    }
);
