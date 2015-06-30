'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'text!pimee/template/product/submit-draft',
        'pim/fetcher-registry',
        'oro/messenger'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        template,
        FetcherRegistry,
        messenger
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
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

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
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
            render: function () {
                if (undefined !== this.draft.get('status')) {
                    this.$el.html(
                        this.template({
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
