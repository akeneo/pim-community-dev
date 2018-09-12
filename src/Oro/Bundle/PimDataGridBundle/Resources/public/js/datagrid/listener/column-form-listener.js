define(
    ['jquery', 'underscore', 'oro/mediator', 'oro/datagrid/column-form-listener'],
    function ($, _, mediator, OroColumnFormListener) {
        'use strict';

        /**
         * Column form listener based on oro implementation that allows
         * changing of field selectors dynamically using mediator
         */
        var ColumnFormListener = OroColumnFormListener.extend({
            $checkbox: null,
            initialize: function () {
                OroColumnFormListener.prototype.initialize.apply(this, arguments);

                this.$checkbox = $('<input type="checkbox">').css('margin', 0);

                mediator.on('datagrid_collection_set_after', function (collection, $grid) {
                    if (collection.inputName === this.gridName) {
                        this.$el = $grid.find('table.grid thead th:not([style])').first();

                        this.$el.empty().html(this.$checkbox);

                        this.setStateFromCollection(collection);

                        this.$checkbox.on('click', _.bind(function () {
                            var state = this.$checkbox.is(':checked');
                            _.each(collection.models, function (model) {
                                model.set(this.columnName, state);
                            }, this);
                        }, this));
                    }
                }, this);

                mediator.on('grid_load:complete', function (collection) {
                    if (collection.inputName === this.gridName) {
                        this.setStateFromCollection(collection);
                    }
                }, this);

                mediator.bind('column_form_listener:set_selectors:' + this.gridName, function (selectors) {
                    this._clearState();
                    this.selectors = selectors;
                    this._restoreState();
                    this._synchronizeState();
                }, this);

                mediator.trigger('column_form_listener:initialized', this.gridName);
            },

            _explode: function (string) {
                if (!string) {
                    return [];
                }
                return _.map(string.split(','), function (val) {
                    return val ? String(val).trim() : null;
                });
            },

            setStateFromCollection: function (collection) {
                var checked = true;
                _.each(collection.models, function (model) {
                    if (checked) {
                        checked = model.get(this.columnName);
                    }
                }, this);
                this.$checkbox.prop('checked', checked);
            },

            _processValue: function (id, model) {
                OroColumnFormListener.prototype._processValue.apply(this, arguments);

                var selectEvent = model.get(this.columnName) ? 'selectModel' : 'unselectModel';
                mediator.trigger('datagrid:' + selectEvent + ':' + this.gridName, model);
            }
        });

        return {
            init: function ($gridContainer, gridName) {
                var metadata = $gridContainer.data('metadata');
                var options = metadata.options || {};
                if (options.columnListener) {
                    options.columnListener.selectors = options.columnListener.selectors || {};
                    new ColumnFormListener(_.extend({ $gridContainer: $gridContainer, gridName: gridName }, options.columnListener));
                }
            }
        };
    }
);
