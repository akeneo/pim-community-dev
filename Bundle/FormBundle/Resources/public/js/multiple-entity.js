/* jshint devel:true */
/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/multiple-entity/view', 'oro/widget-manager', 'oro/dialog-widget'],
function(_, Backbone, __, EntityView, WidgetManager, DialogWidget) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/multiple-entity
     * @class   oro.multipleEntity
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            collection: null,
            selectionUrl: null,
            addedElement: null,
            removedElement: null,
            defaultElementName: null
        },

        initialize: function() {
            this.listenTo(this.getCollection(), 'add', this.addEntity);
            this.listenTo(this.getCollection(), 'reset', this.addAll);

            this.$addedEl = $(this.options.addedElement);
            this.$removedEl = $(this.options.removedElement);

            this.render();
        },

        getCollection: function() {
            return this.options.collection;
        },

        addAll: function(items) {
            this.$entitiesContainer.empty();
            items.each(function(item) {
                this.addEntity(item);
            }, this);
        },

        addEntity: function(item) {
            var entityView = new EntityView({
                defaultElementName: this.options.defaultElementName,
                model: item
            });
            this.$entitiesContainer.append(entityView.render().$el);
        },

        addEntities: function() {
            if (!this.selectorDialog) {
                this.selectorDialog = new DialogWidget({
                    url: this.options.selectionUrl,
                    title: __('Select Contacts'),
                    dialogOptions: {
                        'modal': true,
                        'stateEnabled': false,
                        'width': 750,
                        'autoResize':true,
                        'close': _.bind(function() {
                            delete this.selectorDialog;
                        }, this)
                    }
                });
                WidgetManager.addWidgetInstance(this.selectorDialog);
                this.selectorDialog.on('completeSelection', _.bind(this.processSelectedEntities, this));
                this.selectorDialog.render();
            }
        },

        processSelectedEntities: function(added, removed) {
            console.log(arguments);
        },

        render: function() {
            this.$el.empty();
            this.$entitiesContainer = $('<div class="entities"/>').appendTo(this.$el);
            $('<button type="button" class="btn btn-medium"><i class="icon-plus"></i>' + __('Add Contacts') + '</button>')
                .click(_.bind(this.addEntities, this))
                .appendTo(
                    $('<div class="actions"/>')
                        .appendTo(this.$el)
                );
            return this;
        }
    });
});
