define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/mediator', 'oro/loading-mask', 'pim/dialog'],
    function ($, _, Backbone, __, Routing, mediator, LoadingMask, Dialog) {
        'use strict';

        return Backbone.View.extend({
            tagName: 'table',
            template: '',
            events: {},
            $target: null,
            itemViews: [],
            url: '',
            collectionClass: null,
            itemClass: null,
            itemViewClass: null,
            rendered: false,
            initialize: function (options) {
                this.$target         = options.$target;
                this.collectionClass = options.collectionClass;
                this.itemClass       = options.itemClass;
                this.itemViewClass   = options.itemViewClass;
                this.url             = options.url;
                this.collection      = new this.collectionClass({url: options.url});
                this.render();

                this.load();
            },
            render: function () {
                this.$el.empty();
                this.$el.html(this.renderTemplate());

                _.each(this.collection.models, function (ruleItem) {
                    this.addItem({item: ruleItem});
                }.bind(this));

                if (!this.rendered) {
                    this.$target.html(this.$el);

                    this.rendered = true;
                }

                return this;
            },
            renderTemplate: function () {
                return this.template({});
            },
            load: function () {
                this.itemViews = [];
                this.inLoading(true);
                this.collection
                    .fetch({
                        success: function () {
                            this.inLoading(false);
                            this.render();
                        }.bind(this)
                    });
            },
            addItem: function (opts) {
                var options = opts || {};

                var newItemView = this.createItemView(options.item);

                if (newItemView) {
                    this.$el.children('tbody').append(newItemView.$el);
                }
            },
            createItemView: function (item) {
                var itemView = new this.itemViewClass({
                    model:    item,
                    parent:   this
                });

                itemView.showReadableItem();

                this.collection.add(item);
                this.itemViews.push(itemView);

                return itemView;
            },
            deleteItem: function (item) {
                this.inLoading(true);

                item.model.destroy({
                    success: function () {
                        this.inLoading(false);

                        this.collection.remove(item);

                        if (0 === this.collection.length) {
                            this.render();
                            item.$el.hide(0);
                        } else if (!item.model.id) {
                            item.$el.hide(0);
                        } else {
                            item.$el.hide(500);
                        }
                    }.bind(this),
                    error: function (data, response) {
                        this.inLoading(false);
                        var message;

                        if (response.responseJSON) {
                            message = response.responseJSON;
                        } else {
                            message = response.responseText;
                        }

                        Dialog.alert(message, __('pim_enrich.item.list.delete.error'));
                    }.bind(this)
                });
            },
            inLoading: function (loading) {
                if (loading) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.$el);
                    loadingMask.show();
                } else {
                    this.$el.find('.loading-mask').remove();
                }
            }
        });
    }
);

