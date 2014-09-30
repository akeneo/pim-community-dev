define(
    ['jquery', 'underscore', 'backbone', 'routing'],
    function ($, _, Backbone, Routing) {
        'use strict';

        var getAttributeOptions = function (attributeId) {
            var url = Routing.generate('pim_enrich_attributeoption_index', {attribute_id: attributeId});

            return $.get(url);
        }

        var AttributeOptionItem = Backbone.Model.extend({
            defaults: {
                code: '',
                default : false,
                translatable: true,
                sort_order: 0
            }
        });

        var AttributeOptionValueCollection = Backbone.Model.extend({
            defaults: {
                value : ''
            }
        });


        var AttributeOptionCollection = Backbone.Collection.extend({
            model: AttributeOptionItem,
            initialize: function(options) {
                this.url = options.url;
            }
        });

        var EditableItemView = Backbone.View.extend({
            tagName: 'tr',
            className: 'editable-item-row',
            showTemplate: _.template(
                '<td>' +
                    '<%= item.code %>' +
                '</td>' +
                '<td class="action">' +
                    '<span class="btn btn-small edit-row"><i class="icon-pencil"></i></span>' +
                    '<span class="btn btn-small delete-row"><i class="icon-trash"></i></span>' +
                '</td>'
            ),
            editTemplate: _.template(
                '<td>' +
                    '<input type="text" id="code" value="<%= item.code %>" />' +
                '</td>' +
                '<td class="action">' +
                    '<span class="btn btn-small update-row"><i class="icon-ok"></i></span>' +
                    '<span class="btn btn-small show-row"><i class="icon-remove"></i></span>' +
                '</td>'
            ),
            events: {
                'click .action .show-row':   'showItem',
                'click .action .edit-row':   'editItem',
                'click .action .delete-row': 'deleteItem',
                'click .action .update-row': 'updateItem',
                'change input':              'soil'
            },
            options: null,
            editable: true,
            parent: null,
            initialize: function(options) {
                this.options = options;

                this.render();
            },
            render: function() {
                var content = '';

                console.log(this);

                if (this.editable) {
                    this.clean();
                    content = this.editTemplate({item: this.model.toJSON()})
                } else {
                    content = this.showTemplate({item: this.model.toJSON()})
                }

                this.$el.html(content);

                return this;
            },
            showItem: function(e) {
                this.editable = false;
                this.clean();
                console.log('test');
                this.render();
            },
            editItem: function(e) {
                var rowIsEditable = this.options.parent.requestRowEdition(this);

                if (rowIsEditable) {
                    this.editable = true;
                    this.render();
                }
            },
            deleteItem: function(e) {
                this.options.parent.deleteItem(this);
                this.model.destroy({
                    success: function() {
                    }
                });
            },
            updateItem: function(e) {
                this.loadModelFromView();
                this.model.save(
                    {},
                    {success: _.bind(function(data) { this.showItem();}, this)}
                );
            },
            loadModelFromView: function()
            {
                _.each(this.$el.find('input'), function(input) {
                    this.model.set(input.id, input.value);
                }, this);
            },
            soil: function(e) {
                this.dirty = true;
                this.options.parent.soil();
            },
            clean: function(e) {
                this.dirty = false;
                this.options.parent.clean();
            }
        });

        var AttributeOptionsView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered table-stripped attribute-option-view',
            template: _.template(
                '<thead>' +
                    '<tr>' +
                        '<td>Code</td>' +
                        '<td>Action</td>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                '</tbody>' +
                '<tfoot>' +
                    '<tr><td clospan="2"><span class="btn option-add">Add an option</span></td></tr>' +
                '</tfoot>'
            ),
            events: {
                'click .option-add': 'addAttributeOption'
            },
            $target: null,
            dirty: false,
            attributeOptionItemViews: [],
            initialize: function(options) {
                this.$target = options.$target;
                this.collection = new AttributeOptionCollection({url: options.url});

                this.load();
            },
            render: function() {
                this.$el.empty();

                this.$el.html(this.template());

                _.each(this.collection.models, function(attributeOptionItem) {
                    this.addAttributeOption({
                        'attributeOptionItem': attributeOptionItem
                    });
                }, this);

                if (0 === this.collection.length) {
                    this.addAttributeOption();
                }

                this.$target.html(this.$el);

                return this;
            },
            load: function() {
                this.attributeOptionItemViews = [];
                this.collection
                    .fetch({success: _.bind(this.render, this)});
            },
            createAttributeOptionItem: function() {
                var attributeOptionItem = new AttributeOptionItem();

                this.collection.add(attributeOptionItem);

                return attributeOptionItem;
            },
            addAttributeOption: function(options) {
                var options = options || {};

                if (!options.attributeOptionItem) {
                    var attributeOptionItem = this.createAttributeOptionItem();
                } else {
                    var attributeOptionItem = options.attributeOptionItem;
                }

                var attributeOptionItemView = this.createAttributeOptionItemView(attributeOptionItem);

                this.$el.children('tbody').append(attributeOptionItemView.$el);
            },
            createAttributeOptionItemView: function(attributeOptionItem) {
                var attributeOptionItemView = new EditableItemView({
                    model: attributeOptionItem,
                    url: this.collection.url,
                    parent: this
                });

                this.attributeOptionItemViews.push(attributeOptionItemView);

                return attributeOptionItemView;
            },
            requestRowEdition: function (attributeOptionRow) {
                if (this.dirty) {
                    alert('please register your modifications on other rows before editing an other');

                    return false;
                } else {
                    _.each(this.attributeOptionItemViews, function(attributeOptionItemView) {
                        console.log(attributeOptionItemView);
                        attributeOptionItemView.showItem();
                    }, this);

                    this.dirty = false;
                }

                return true;
            },
            deleteItem: function(item) {
                item.model.destroy({
                    success: _.bind(function() {
                        this.collection.remove(item);

                        if (0 === this.collection.length) {
                            this.addAttributeOption();
                            item.$el.hide(0);
                        } else {
                            item.$el.hide(500);
                        }
                    }, this)
                });


            },
            soil: function() {
                this.dirty = true;
            },
            clean: function() {
                this.dirty = false;
            }
        });

        return function($element) {
            var attributeOptionsView = new AttributeOptionsView({
                $target: $element,
                url: Routing.generate(
                    'pim_enrich_attributeoption_index',
                    {attribute_id: $element.data('attribute-id')}
                )
            });
        };
    }
);
