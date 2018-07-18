define(
    ['backbone', 'underscore', 'oro/translator', 'pim/dialog'],
    function (Backbone, _, __, Dialog) {
        'use strict';

        return Backbone.View.extend({
            tagName: 'tr',
            template: '',
            itemName: 'item',
            events: {
                'click .delete-row': 'deleteItem'
            },
            parent: null,
            loading: false,
            initialize: function (options) {
                this.parent    = options.parent;
                this.model.rootUrl = this.parent.url;

                this.render();
            },
            render: function () {
                this.$el.html(this.renderTemplate());

                this.$el.attr('data-item-id', this.model.id);

                return this;
            },
            renderTemplate: function () {
                return this.template({});
            },
            showReadableItem: function () {
                this.render();
            },
            deleteItem: function () {
                Dialog.confirm(
                    __('pim_enrich.entity.fallback.module.delete.item_placeholder', {'itemName': this.itemName}),
                    __('pim_enrich.entity.fallback.module.delete.title', {'itemName': this.itemName}),
                    function () {
                        this.parent.deleteItem(this);
                    }.bind(this)
                );
            },
            inLoading: function (loading) {
                this.parent.inLoading(loading);
            }
        });
    }
);
