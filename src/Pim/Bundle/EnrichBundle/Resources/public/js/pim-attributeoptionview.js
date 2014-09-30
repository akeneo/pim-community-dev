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
                sort_order: 0,
                optionValues: {}
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
                '<% _.each(locales, function(locale) { %>' +
                '<td>' +
                    '<% if (item.optionValues[locale]) { %>' +
                        '<%= item.optionValues[locale].value %>' +
                    '<% } %>' +
                '</td>' +
                '<% }); %>' +
                '<td>' +
                '<% if (item.default) { %>' +
                    'default' +
                '<% } %>' +
                '</td>' +
                '<td>' +
                    '<span class="btn btn-small edit-row"><i class="icon-pencil"></i></span>' +
                    '<span class="btn btn-small delete-row"><i class="icon-trash"></i></span>' +
                '</td>'
            ),
            editTemplate: _.template(
                '<td class="field-cell">' +
                    '<input type="text" id="code" value="<%= item.code %>" />' +
                '</td>' +
                '<% _.each(locales, function(locale) { %>' +
                '<td class="field-cell">' +
                    '<% if (item.optionValues[locale]) { %>' +
                        '<input type="text" class="attribute-option-value" data-locale="<%= locale %>" value="<%= item.optionValues[locale].value %>" />' +
                    '<% } else { %>' +
                        '<input type="text" class="attribute-option-value" data-locale="<%= locale %>" value="" />' +
                    '<% } %>' +
                '</td>' +
                '<% }); %>' +
                '<td>' +
                    '<input type="checkbox" class="is-default" checked="<%= item.default ? \'on\' : \'\' %>" />' +
                '</td>' +
                '<td>' +
                    '<span class="btn btn-small update-row"><i class="icon-ok"></i></span>' +
                    '<span class="btn btn-small show-row"><i class="icon-remove"></i></span>' +
                '</td>'
            ),
            events: {
                'click .show-row':   'showItem',
                'click .edit-row':   'editItem',
                'click .delete-row': 'deleteItem',
                'click .update-row': 'updateItem',
                'change input':      'soil'
            },
            editable: false,
            parent: null,
            locales: [],
            initialize: function(options) {
                this.locales  = options.locales;
                this.parent   = options.parent;

                this.render();
            },
            render: function() {
                var template = null;

                if (this.editable) {
                    this.clean();
                    this.$el.addClass('editable');
                    template = this.editTemplate;
                } else {
                    this.$el.removeClass('editable');
                    template = this.showTemplate;
                }

                this.$el.html(template({
                    item: this.model.toJSON(),
                    locales: this.locales
                }));

                return this;
            },
            showItem: function(e) {
                this.editable = false;
                this.clean();

                this.render();
            },
            editItem: function(e) {
                var rowIsEditable = this.parent.requestRowEdition(this);

                if (rowIsEditable) {
                    this.editable = true;
                    this.render();
                }
            },
            deleteItem: function(e) {
                this.parent.deleteItem(this);
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
                var attributeOptions = {};
                _.each(this.$el.find('.attribute-option-value'), function(input) {
                    var locale = input.dataset.locale;

                    attributeOptions[locale] = {
                        locale: locale,
                        value:  input.value,
                        id:     this.model.get('optionValues')[locale].id
                    };
                }, this);

                this.model.set('code', this.$el.find('#code').val())
                this.model.set('optionValues', attributeOptions);
            },
            soil: function(e) {
                this.dirty = true;
            },
            clean: function(e) {
                this.dirty = false;
            }
        });

        var AttributeOptionsView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered table-stripped attribute-option-view',
            template: _.template(
                '<colgroup>' +
                    '<col class="fields" span="<%= 1 + locales.length %>"></col>' +
                    '<col class="default" span="1"></col>' +
                    '<col class="action" span="1"></col>' +
                '</colgroup>' +
                '<thead>' +
                    '<tr>' +
                        '<th>Code</th>' +
                        '<% _.each(locales, function(locale) { %>' +
                        '<th>' +
                            '<%= locale %>' +
                        '</th>' +
                        '<% }); %>' +
                        '<th>Default</th>' +
                        '<th>Action</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                '</tbody>' +
                '<tfoot>' +
                    '<tr><td colspan="<%= 3 + locales.length %>"><span class="btn option-add">Add an option</span></td></tr>' +
                '</tfoot>'
            ),
            events: {
                'click .option-add': 'addAttributeOption'
            },
            $target: null,
            locales: [],
            editableItemRow: null,
            attributeOptionItemViews: [],
            initialize: function(options) {
                this.$target    = options.$target;
                this.collection = new AttributeOptionCollection({url: options.url});
                this.locales    = options.locales;

                this.load();
            },
            render: function() {
                this.$el.empty();
                this.$el.html(this.template({
                    locales: this.locales
                }));

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
                var create = !options.attributeOptionItem;

                if (create) {
                    var attributeOptionItem = this.createAttributeOptionItem();
                } else {
                    var attributeOptionItem = options.attributeOptionItem;
                }

                var attributeOptionItemView = this.createAttributeOptionItemView(
                    attributeOptionItem,
                    create
                );

                this.$el.children('tbody').append(attributeOptionItemView.$el);
            },
            createAttributeOptionItemView: function(attributeOptionItem, editable) {
                var attributeOptionItemView = new EditableItemView({
                    model:    attributeOptionItem,
                    url:      this.collection.url,
                    locales:  this.locales,
                    parent:   this
                });

                if (editable) {
                    attributeOptionItemView.editItem();
                }

                this.attributeOptionItemViews.push(attributeOptionItemView);

                return attributeOptionItemView;
            },
            requestRowEdition: function (attributeOptionRow) {
                if (this.editableItemRow && this.editableItemRow.dirty) {
                    alert('please register your modifications on other rows before editing an other');

                    return false;
                }

                this.editableItemRow = attributeOptionRow;

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


            }
        });

        return function($element) {
            var attributeOptionsView = new AttributeOptionsView(
            {
                $target: $element,
                url: Routing.generate(
                    'pim_enrich_attributeoption_index',
                    {attribute_id: $element.data('attribute-id')}
                ),
                locales: $element.data('locales')
            });
        };
    }
);
