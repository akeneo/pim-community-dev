define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator',
        'routing',
        'oro/mediator',
        'oro/loading-mask',
        'pim/dialog',
        'pim/template/attribute-option/index',
        'pim/template/attribute-option/edit',
        'pim/template/attribute-option/show',
        'oro/messenger',
        'jquery-ui'
    ],
    function (
        $,
        _,
        Backbone,
        __,
        Routing,
        mediator,
        LoadingMask,
        Dialog,
        indexTemplate,
        editTemplate,
        showTemplate,
        messenger
    ) {
        'use strict';

        var AttributeOptionItem = Backbone.Model.extend({
            defaults: {
                code: '',
                optionValues: {}
            }
        });

        var ItemCollection = Backbone.Collection.extend({
            model: AttributeOptionItem,
            initialize: function (options) {
                this.url = options.url;
            }
        });

        var EditableItemView = Backbone.View.extend({
            tagName: 'tr',
            className: 'AknGrid-bodyRow editable-item-row',
            showTemplate: _.template(showTemplate),
            editTemplate: _.template(editTemplate),
            events: {
                'click .show-row':   'stopEditItem',
                'click .edit-row':   'startEditItem',
                'click .delete-row': 'deleteItem',
                'click .update-row': 'updateItem',
                'keyup input':       'soil',
                'keydown':           'cancelSubmit'
            },
            editable: false,
            parent: null,
            loading: false,
            locales: [],
            initialize: function (options) {
                this.locales       = options.locales;
                this.parent        = options.parent;
                this.model.urlRoot = this.parent.updateUrl;

                this.render();
            },
            render: function () {
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
            showReadableItem: function () {
                this.editable = false;
                this.parent.showReadableItem(this);
                this.clean();
                this.render();
            },
            showEditableItem: function () {
                this.editable = true;
                this.render();
                this.model.set(this.loadModelFromView().attributes);
            },
            startEditItem: function () {
                var rowIsEditable = this.parent.requestRowEdition(this);

                if (rowIsEditable) {
                    this.showEditableItem();
                }
            },
            stopEditItem: function () {
                if (!this.model.id || this.dirty) {
                    if (this.dirty) {
                        Dialog.confirm(
                            __('pim_enrich.entity.attribute_option.module.edit.cancel_description'),
                            __('pim_enrich.entity.attribute_option.module.edit.cancel_title'),
                            function () {
                                this.showReadableItem(this);
                                if (!this.model.id) {
                                    this.deleteItem();
                                }
                            }.bind(this));
                    } else {
                        if (!this.model.id) {
                            this.deleteItem();
                        } else {
                            this.showReadableItem();
                        }
                    }
                } else {
                    this.showReadableItem();
                }
            },
            deleteItem: function () {
                var itemCode = this.el.firstChild.innerText;

                Dialog.confirmDelete(
                    __('pim_enrich.entity.fallback.module.delete.item_placeholder', {'itemName': itemCode}),
                    __('pim_enrich.entity.fallback.module.delete.title', {'itemName': itemCode}),
                    function () {
                        this.parent.deleteItem(this);
                    }.bind(this),
                    __('pim_enrich.entity.attribute.plural_label')
                );
            },
            updateItem: function () {
                this.inLoading(true);

                var editedModel = this.loadModelFromView();

                editedModel.save(
                    {},
                    {
                        url: this.model.url(),
                        success: function () {
                            this.inLoading(false);
                            this.model.set(editedModel.attributes);
                            this.clean();
                            this.stopEditItem();
                            if (!this.parent.sortable) {
                                this.parent.render();
                            } else {
                                this.parent.updateSorting();
                            }
                        }.bind(this),
                        error: this.showValidationErrors.bind(this)
                    }
                );
            },
            showValidationErrors: function (data, xhr) {
                this.inLoading(false);

                var response = xhr.responseJSON;

                if (response.code) {
                    this.$el.find('.validation-tooltip')
                        .attr('data-original-title', response.code)
                        .removeClass('AknIconButton--hide')
                        .tooltip('destroy')
                        .tooltip('show');
                } else {
                    messenger.notify('error', response.optionValues);
                }
            },
            cancelSubmit: function (e) {
                if (e.keyCode === 13) {
                    this.updateItem();

                    return false;
                }
            },
            loadModelFromView: function () {
                var attributeOptions = {};
                var editedModel = this.model.clone();

                editedModel.urlRoot = this.model.urlRoot;

                _.each(this.$el.find('.attribute-option-value'), function (input) {
                    var locale = input.dataset.locale;

                    attributeOptions[locale] = {
                        locale: locale,
                        value:  input.value,
                        id:     this.model.get('optionValues')[locale] ?
                            this.model.get('optionValues')[locale].id :
                            null
                    };
                }.bind(this));

                editedModel.set('code', this.$el.find('.attribute_option_code').val());
                editedModel.set('optionValues', attributeOptions);

                return editedModel;
            },
            inLoading: function (loading) {
                this.parent.inLoading(loading);
            },
            soil: function () {
                if (JSON.stringify(this.model.attributes) !== JSON.stringify(this.loadModelFromView().attributes)) {
                    this.dirty = true;
                } else {
                    this.dirty = false;
                }
            },
            clean: function () {
                this.dirty = false;
            }
        });

        var ItemCollectionView = Backbone.View.extend({
            tagName: 'table',
            className: 'AknGrid AknGrid--unclickable table attribute-option-view',
            template: _.template(indexTemplate),
            events: {
                'click .option-add': 'addItem'
            },
            $target: null,
            locales: [],
            sortable: true,
            sortingUrl: '',
            updateUrl: '',
            currentlyEditedItemView: null,
            itemViews: [],
            rendered: false,
            initialize: function (options) {
                this.$target    = options.$target;
                this.collection = new ItemCollection({url: options.updateUrl});
                this.locales    = options.locales;
                this.updateUrl  = options.updateUrl;
                this.sortingUrl = options.sortingUrl;
                this.sortable   = options.sortable;

                this.render();
                this.load();
            },
            render: function () {
                this.$el.empty();

                this.currentlyEditedItemView = null;
                this.updateEditionStatus();

                this.$el.html(this.template({
                    locales: this.locales,
                    add_option_label: __('pim_enrich.entity.product.module.attribute.add_option'),
                    code_label: __('pim_common.code')
                }));

                _.each(_.sortBy(this.collection.models, function (attributeOptionItem) {
                    return this.sortable ? 0 : attributeOptionItem.attributes.code;
                }.bind(this)), function (attributeOptionItem) {
                    this.addItem({item: attributeOptionItem});
                }.bind(this));

                if (0 === this.collection.length) {
                    this.addItem();
                }

                if (!this.rendered) {
                    this.$target.html(this.$el);

                    this.rendered = true;
                }

                this.setSortable();
                this.updateSortableStatus(this.sortable);

                return this;
            },
            setSortable: function() {
                this.$el.sortable({
                    items: 'tbody tr',
                    handle: '.handle',
                    axis: 'y',
                    connectWith: this.$el,
                    containment: this.$el,
                    distance: 5,
                    cursor: 'move',
                    helper: function (e, ui) {
                        ui.children().each(function () {
                            $(this).width($(this).width());
                        });

                        return ui;
                    },
                    stop: function () {
                        this.updateSorting();
                    }.bind(this)
                });
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

                //If no item model provided we create one
                var itemToAdd;
                if (!options.item) {
                    itemToAdd = new AttributeOptionItem();
                } else {
                    itemToAdd = options.item;
                }

                var newItemView = this.createItemView(itemToAdd);

                if (newItemView) {
                    this.$el.children('tbody').append(newItemView.$el);
                }
            },
            createItemView: function (item) {
                var itemView = new EditableItemView({
                    model:    item,
                    url:      this.updateUrl,
                    locales:  this.locales,
                    parent:   this
                });

                //If the item is new the view is changed to edit mode
                if (!item.id) {
                    if (!this.requestRowEdition(itemView)) {
                        return;
                    } else {
                        itemView.showEditableItem();
                    }
                }

                this.collection.add(item);
                this.itemViews.push(itemView);

                return itemView;
            },
            requestRowEdition: function (attributeOptionRow) {
                if (this.currentlyEditedItemView) {
                    if (this.currentlyEditedItemView.dirty) {
                        messenger.notify('error', __('alert.attribute_option.save_before_edit_other'));

                        return false;
                    } else {
                        this.currentlyEditedItemView.stopEditItem();
                        this.currentlyEditedItemView = null;
                        this.updateEditionStatus();
                    }
                }

                if (attributeOptionRow.model.id) {
                    this.currentlyEditedItemView = attributeOptionRow;
                }

                this.updateEditionStatus();

                return true;
            },
            showReadableItem: function (item) {
                if (item === this.currentlyEditedItemView) {
                    this.currentlyEditedItemView = null;
                    this.updateEditionStatus();
                }
            },
            deleteItem: function (item) {
                this.inLoading(true);

                item.model.destroy({
                    success: function () {
                        this.inLoading(false);

                        this.collection.remove(item);
                        this.currentlyEditedItemView = null;
                        this.updateEditionStatus();

                        if (0 === this.collection.length) {
                            this.addItem();
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
                            message = response.responseJSON.message;
                        } else {
                            message = response.responseText;
                        }

                        messenger.notify('error', message);
                    }.bind(this)
                });
            },
            updateEditionStatus: function () {
                if (this.currentlyEditedItemView) {
                    this.$el.addClass('in-edition');
                } else {
                    this.$el.removeClass('in-edition');
                }
            },
            updateSortableStatus: function (sortable) {
                this.sortable = sortable;

                if (sortable) {
                    this.$el.sortable('enable');
                } else {
                    this.$el.sortable('disable');
                }
            },
            updateSorting: function () {
                var sorting = [];

                var rows = this.$el.find('tbody tr');
                for (var i = rows.length - 1; i >= 0; i--) {
                    sorting[i] = rows[i].dataset.itemId;
                }

                $.ajax({
                    url: this.sortingUrl,
                    type: 'PUT',
                    data: JSON.stringify(sorting)
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

        return function ($element) {
            var itemCollectionView = new ItemCollectionView(
            {
                $target: $element,
                updateUrl: Routing.generate(
                    'pim_enrich_attributeoption_index',
                    {attributeId: $element.data('attribute-id')}
                ),
                sortingUrl: Routing.generate(
                    'pim_enrich_attributeoption_update_sorting',
                    {attributeId: $element.data('attribute-id')}
                ),
                locales: $element.data('locales'),
                sortable: $element.data('sortable')
            });

            mediator.on('attribute:auto_option_sorting:changed', function (autoSorting) {
                itemCollectionView.setSortable();
                itemCollectionView.updateSortableStatus(!autoSorting);
                itemCollectionView.render();
            }.bind(this));
        };
    }
);
