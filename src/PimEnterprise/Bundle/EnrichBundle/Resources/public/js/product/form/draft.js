'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'text!pimee/template/product/submit-draft',
        'pimee/permission-manager',
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
        PermissionManager,
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
                this.listenTo(mediator, 'product:action:post_update', this.reloadProductDraft);

                this.listenTo(this.getRoot(), 'pre_set_data', this.loadProductDraft);

                return $.when(
                    PermissionManager.getPermissions().then(_.bind(function (permissions) {
                        this.permissions = permissions;
                    }, this)),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            loadProductDraft: function (event) {
                var categories = event.data.categories;
                var isOwner = !categories.length ||
                    !!_.intersection(this.permissions.categories.OWN_PRODUCTS, categories).length;

                if (isOwner) {
                    return;
                }

                FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(event.data.meta.id)
                    .then(_.bind(function (data) {
                        this.updateProductDraft(data);
                    }, this));
            },
            reloadProductDraft: function (data) {
                FetcherRegistry.getFetcher('product-draft').clear(data.meta.id);
                this.loadProductDraft({data: this.getData()});
            },
            updateProductDraft: function (data) {
                this.draft.set(data);

                if ('state' in this.parent.extensions) {
                    this.parent.extensions.state.state = undefined;
                    this.parent.extensions.state.collectState();
                }

                var draft = this.draft.toJSON();
                if (draft.changes) {
                    this.getRoot().model.set(
                        'values',
                        _.extend(
                            this.getRoot().model.get('values') || {},
                            this.draft.toJSON().changes.values
                        )
                    ).trigger('change');
                }
            },
            render: function () {
                if (undefined !== this.draft.get('status')) {
                    this.$el.html(
                        this.template({
                            'submitted': this.draft.get('status') !== 0
                        })
                    );
                    this.delegateEvents();
                    this.$el.show();
                } else {
                    this.$el.hide();
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
