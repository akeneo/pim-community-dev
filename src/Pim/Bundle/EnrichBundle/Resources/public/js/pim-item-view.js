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
                    __('confirmation.remove.item_placeholder', {'itemName': this.itemName}),
                    __('confirmation.remove.title', {'itemName': this.itemName}),
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
