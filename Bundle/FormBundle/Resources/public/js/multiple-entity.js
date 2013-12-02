/* jshint devel:true */
/* global define */
define(['underscore', 'backbone', 'oro/multiple-entity/view', 'oro/multiple-entity/model', 'oro/dialog-widget'],
function(_, Backbone, EntityView, MultipleEntityModel, DialogWidget) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/multiple-entity
     * @class   oro.multipleEntity
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            template: null,
            elementTemplate: null,
            entitiesContainerSelector: '.entities',
            name: null,
            collection: null,
            selectionUrl: null,
            addedElement: null,
            removedElement: null,
            defaultElement: null,
            itemsPerRow: 4,
            selectorWindowTitle: null
        },

        events: {
            'click .add-btn': 'addEntities'
        },

        initialize: function() {
            this.template = _.template(this.options.template)
            this.listenTo(this.getCollection(), 'add', this.addEntity);
            this.listenTo(this.getCollection(), 'reset', this.addAll);
            this.listenTo(this.getCollection(), 'remove', this.removeDefault);

            this.$addedEl = $(this.options.addedElement);
            this.$removedEl = $(this.options.removedElement);
            if (this.options.defaultElement) {
                this.listenTo(this.getCollection(), 'defaultChange', this.updateDefault);
                this.$defaultEl = this.$el.closest('form').find('[name$="[' + this.options.defaultElement + ']"]');
            }

            this.render();
        },

        handleRemove: function(item) {
            var removedElVal = this.$removedEl.val();
            var removed = removedElVal ? this.$removedEl.val().split(',') : [];
            if (item.get('id') && removed.indexOf(item.get('id')) === -1) {
                removed.push(item.get('id'));
                this.$removedEl.val(removed.join(','));
            }
        },

        removeDefault: function(item) {
            if (item.get('isDefault')) {
                this.$defaultEl.val('');
            }
        },

        updateDefault: function(item) {
            this.$defaultEl.val(item.get('id'));
        },

        getCollection: function() {
            return this.options.collection;
        },

        addAll: function(items) {
            this._resortCollection();
            this.$entitiesContainer.empty();
            items.each(function(item) {
                this.addEntity(item);
            }, this);
        },

        _resortCollection: function() {
            this.getCollection().comparator = function(model) {
                if (model.get('isDefault')) {
                    return 'A';
                } else {
                    return model.get('label');
                }
            };
            this.getCollection().sort();
        },

        addEntity: function(item) {
            if (item.get('id') == this.$defaultEl.val()) {
                item.set('isDefault', true);
            }
            var entityView = new EntityView({
                model: item,
                name: this.options.name,
                hasDefault: this.options.defaultElement,
                template: this.options.elementTemplate
            });
            entityView.on('removal', _.bind(this.handleRemove, this));
            this.$entitiesContainer.append(entityView.render().$el);
        },

        addEntities: function() {
            if (!this.selectorDialog) {
                var url = this.options.selectionUrl;
                var separator = url.indexOf('?') > -1 ? '&' : '?';
                this.selectorDialog = new DialogWidget({
                    url: url + separator
                        + 'added=' + this.$addedEl.val()
                        + '&removed=' + this.$removedEl.val()
                        + '&default=' + this.$defaultEl.val(),
                    title: this.options.selectorWindowTitle,
                    stateEnabled: false,
                    dialogOptions: {
                        'modal': true,
                        'width': 1024,
                        'height': 500,
                        'close': _.bind(function() {
                            this.selectorDialog = null;
                        }, this)
                    }
                });
                this.selectorDialog.on('completeSelection', _.bind(this.processSelectedEntities, this));
                this.selectorDialog.render();
            }
        },

        processSelectedEntities: function(added, addedModels, removed) {
            this.$addedEl.val(added.join(','));
            this.$removedEl.val(removed.join(','));

            _.each(addedModels, _.bind(function(model) {
                this.getCollection().add(model);
            }, this));
            for (var i = 0; i < removed.length; i++) {
                var model = this.getCollection().get(removed[i]);
                if (model) {
                    model.set('id', null);
                    model.destroy()
                }
            }

            this.selectorDialog.remove();
        },

        render: function() {
            this.$el.html(this.template());
            this.$entitiesContainer = this.$el.find(this.options.entitiesContainerSelector);

            return this;
        }
    });
});
