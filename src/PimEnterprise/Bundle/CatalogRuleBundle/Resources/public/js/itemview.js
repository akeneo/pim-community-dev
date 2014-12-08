define(
    ['backbone'],
    function (Backbone) {
        'use strict';

        return Backbone.View.extend({
            tagName: 'tr',
            className: '',
            showTemplate: '',
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
                this.parent.deleteItem(this);
            },
            inLoading: function (loading) {
                this.parent.inLoading(loading);
            }
        });
    }
);
