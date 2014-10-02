define(
    ['jquery', 'underscore', 'backbone', 'routing', 'oro/mediator', 'oro/loading-mask', 'jquery-ui-full'],
    function ($, _, Backbone, Routing, mediator, LoadingMask) {
        'use strict';

        var getAttributeOptions = function (attributeId) {
            var url = Routing.generate('pim_enrich_attributeoption_index', {attribute_id: attributeId});

            return $.get(url);
        }

        var AttributeOptionItem = Backbone.Model.extend({
            defaults: {
                code: '',
                translatable: true,
                sort_order: 0,
                optionValues: {}
            }
        });

        var AttributeOptionValueCollection = Backbone.Model.extend();


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
                    '<span class="handle"><i class="icon-reorder"></i></span>' +
                    '<%= item.code %>' +
                '</td>' +
                '<% _.each(locales, function(locale) { %>' +
                '<td >' +
                    '<% if (item.optionValues[locale]) { %>' +
                        '<span title="<%= item.optionValues[locale].value %>"><%= item.optionValues[locale].value %></span>' +
                    '<% } %>' +
                '</td>' +
                '<% }); %>' +
                '<td>' +
                    '<span class="btn btn-small edit-row"><i class="icon-pencil"></i></span>' +
                    '<span class="btn btn-small delete-row"><i class="icon-trash"></i></span>' +
                '</td>'
            ),
            editTemplate: _.template(
                '<td class="field-cell">' +
                    '<input type="text" id="code" value="<%= item.code %>" class="exclude" />' +
                    '<i class="validation-tooltip hidden" data-placement="top" data-toggle="tooltip"></i>' +
                '</td>' +
                '<% _.each(locales, function(locale) { %>' +
                '<td class="field-cell">' +
                    '<% if (item.optionValues[locale]) { %>' +
                        '<input type="text" class="attribute-option-value" data-locale="<%= locale %>" value="<%= item.optionValues[locale].value %>" class="exclude" />' +
                    '<% } else { %>' +
                        '<input type="text" class="attribute-option-value" data-locale="<%= locale %>" value="" class="exclude"/>' +
                    '<% } %>' +
                '</td>' +
                '<% }); %>' +
                '<td>' +
                    '<span class="btn btn-small update-row"><i class="icon-ok"></i></span>' +
                    '<span class="btn btn-small show-row"><i class="icon-remove"></i></span>' +
                '</td>'
            ),
            events: {
                'click .show-row':   'stopEditItem',
                'click .edit-row':   'startEditItem',
                'click .delete-row': 'deleteItem',
                'click .update-row': 'updateItem',
                'change input':      'soil',
                'keydown':           'cancelSubmit'
            },
            editable: false,
            parent: null,
            loading: false,
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
                    this.$el.addClass('in-edition');
                    template = this.editTemplate;
                } else {
                    this.$el.removeClass('in-edition');
                    template = this.showTemplate;
                }

                this.$el.html(template({
                    item: this.model.toJSON(),
                    locales: this.locales
                }));

                this.$el.attr('data-item-id', this.model.id);

                return this;
            },
            showReadableItem: function() {
                this.editable = false;
                this.parent.showReadableItem(this);
                this.clean();
                this.render();
            },
            showEditableItem: function() {
                this.editable = true;
                this.render();
            },
            startEditItem: function() {
                var rowIsEditable = this.parent.requestRowEdition(this);

                if (rowIsEditable) {
                    this.showEditableItem();
                }
            },
            stopEditItem: function() {
                if (!this.model.id) {
                    if (confirm('Warning, you will lose usaved data. Are you sure you want to cancel modification on this new option ?')) {
                        this.showReadableItem(this);

                        this.deleteItem(this);
                    } else {
                        return;
                    }
                } else {
                    this.showReadableItem();
                }
            },
            deleteItem: function() {
                this.parent.deleteItem(this);
            },
            updateItem: function() {
                this.inLoading(true);

                this.loadModelFromView();
                this.model.save(
                    {},
                    {
                        success: _.bind(function(data) {
                            this.inLoading(false);
                            this.stopEditItem();
                        }, this),
                        error: _.bind(function(data, xhr) {
                            this.inLoading(false);

                            var response = xhr.responseJSON;

                            if (response.children &&
                                response.children.code &&
                                response.children.code.errors &&
                                response.children.code.errors.length > 0
                            ) {
                                var message = response.children.code.errors.join('<br/>');
                                console.log(message);
                                this.$el.find('.validation-tooltip')
                                    .addClass('visible')
                                    .tooltip('destroy')
                                    .tooltip({title: message})
                                    .tooltip('show');
                            } else {
                                alert('The attribute option is not valid');
                            }
                        }, this)
                    }
                );
            },
            cancelSubmit: function(e) {
                if(e.keyCode == 13) {
                    this.updateItem();

                    return false;
                }
            },
            loadModelFromView: function()
            {
                var attributeOptions = {};
                _.each(this.$el.find('.attribute-option-value'), function(input) {
                    var locale = input.dataset.locale;

                    attributeOptions[locale] = {
                        locale: locale,
                        value:  input.value,
                        id:     this.model.get('optionValues')[locale] ? this.model.get('optionValues')[locale].id : null
                    };
                }, this);

                this.model.set('code', this.$el.find('#code').val())
                this.model.set('optionValues', attributeOptions);
            },
            inLoading: function(loading) {
                this.parent.inLoading(loading);
            },
            soil: function() {
                this.dirty = true;
            },
            clean: function() {
                this.dirty = false;
            }
        });

        var AttributeOptionsView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered table-stripped attribute-option-view',
            template: _.template(
                '<!-- Pim/Bundle/EnrichBundle/Resources/public/js/pim-attributeoptionview.js -->' +
                '<colgroup>' +
                    '<col class="code" span="1"></col>' +
                    '<col class="fields" span="<%= locales.length %>"></col>' +
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
                        '<th>Action</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                '</tbody>' +
                '<tfoot>' +
                    '<tr><td colspan="<%= 2 + locales.length %>"><span class="btn option-add pull-right">Add an option</span></td></tr>' +
                '</tfoot>'
            ),
            events: {
                'click .option-add': 'addAttributeOption'
            },
            $target: null,
            locales: [],
            loading: false,
            sortable: true,
            sortingUrl: '',
            updateUrl: '',
            currentlyEditedItemView: null,
            attributeOptionItemViews: [],
            initialize: function(options) {
                this.$target    = options.$target;
                this.collection = new AttributeOptionCollection({url: options.updateUrl});
                this.locales    = options.locales;
                this.updateUrl  = options.updateUrl;
                this.sortingUrl = options.sortingUrl;
                this.sortable   = options.sortable;

                mediator.on('attribute:auto_option_sorting:changed', _.bind(function(autoSorting) {
                    this.updateSortableStatus(!autoSorting);
                }, this));

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

                this.$el.sortable({
                    items: "tbody tr",
                    axis: 'y',
                    connectWith: this.$el,
                    containment: this.$el,
                    cursor: 'move',
                    helper: function(e, ui) {
                        ui.children().each(function() {
                            $(this).width($(this).width());
                        });
                        return ui;
                    },
                    stop: _.bind(function(e, ui) {
                        this.updateSorting();
                    }, this)
                });

                this.updateSortableStatus(this.sortable);

                return this;
            },
            load: function() {
                this.attributeOptionItemViews = [];
                this.collection
                    .fetch({success: _.bind(this.render, this)});
            },
            createAttributeOptionItem: function() {
                var attributeOptionItem = new AttributeOptionItem();

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

                if (attributeOptionItemView) {
                    this.$el.children('tbody').append(attributeOptionItemView.$el);
                }
            },
            createAttributeOptionItemView: function(attributeOptionItem, editable) {
                var attributeOptionItemView = new EditableItemView({
                    model:    attributeOptionItem,
                    url:      this.updateUrl,
                    locales:  this.locales,
                    parent:   this
                });

                if (editable) {
                    if (!this.requestRowEdition(attributeOptionItemView)) {
                        return;
                    } else {
                        attributeOptionItemView.showEditableItem();
                    }
                }

                this.collection.add(attributeOptionItem);
                this.attributeOptionItemViews.push(attributeOptionItemView);

                return attributeOptionItemView;
            },
            requestRowEdition: function (attributeOptionRow) {
                if (this.currentlyEditedItemView) {
                    if (this.currentlyEditedItemView.dirty) {
                        alert('please register your modifications on other rows before editing an other');

                        return false;
                    } else {
                        this.currentlyEditedItemView.stopEditItem();
                        this.currentlyEditedItemView = null;
                        this.updateEditionStatus();
                    }
                }

                this.currentlyEditedItemView = attributeOptionRow;
                this.updateEditionStatus();

                return true;
            },
            showReadableItem: function (item) {
                if (item === this.currentlyEditedItemView) {
                    this.currentlyEditedItemView = null;
                    this.updateEditionStatus();
                }
            },
            deleteItem: function(item) {
                this.inLoading(true);

                item.model.destroy({
                    success: _.bind(function() {
                        this.inLoading(false);

                        this.collection.remove(item);

                        if (0 === this.collection.length) {
                            this.addAttributeOption();
                            item.$el.hide(0);
                        } else if (!item.model.id) {
                            item.$el.hide(0);
                        } else {
                            item.$el.hide(500);
                        }
                    }, this)
                });
            },
            updateEditionStatus: function() {
                if (this.currentlyEditedItemView) {
                    this.$el.addClass('in-edition');
                } else {
                    this.$el.removeClass('in-edition');
                }
            },
            updateSortableStatus: function(sortable) {
                this.sortable = sortable;

                if (sortable) {
                    this.$el.sortable('enable');
                } else {
                    this.$el.sortable('disable');
                }
            },
            updateSorting: function() {
                this.inLoading(true);
                var sorting = [];
                var rows = this.$el.find('tbody tr');
                for (var i = rows.length - 1; i >= 0; i--) {
                    sorting[i] = rows[i].dataset.itemId;
                }

                $.ajax({
                    url: this.sortingUrl,
                    type: 'PUT',
                    data: JSON.stringify(sorting)
                }).done(_.bind(function() {
                    this.inLoading(false);
                }, this));
            },
            inLoading: function(loading) {
                this.loading = loading;

                if (this.loading) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.$el);
                    loadingMask.show();
                } else {
                    this.$el.find('.loading-mask').remove();
                }
            }
        });

        return function($element) {
            var attributeOptionsView = new AttributeOptionsView(
            {
                $target: $element,
                updateUrl: Routing.generate(
                    'pim_enrich_attributeoption_index',
                    {attribute_id: $element.data('attribute-id')}
                ),
                sortingUrl: Routing.generate(
                    'pim_enrich_attributeoption_update_sorting',
                    {attribute_id: $element.data('attribute-id')}
                ),
                locales: $element.data('locales'),
                sortable: $element.data('sortable')
            });
        };
    }
);
