define(
    ['oro/mediator', 'oro/datagrid/column-form-listener'],
    function (mediator, OroColumnFormListener) {
        'use strict';

        /**
         * Column form listener based on oro implementation that allows
         * changing of field selectors dynamically using mediator
         */
        var ColumnFormListener = OroColumnFormListener.extend({
            collection: null,
            initialize: function(options) {
                OroColumnFormListener.prototype.initialize.apply(this, arguments);

                mediator.on('datagrid_collection_set_after', function (collection, $grid) {
                    if (collection.inputName === this.gridName) {
                        this.collection = collection;
                        this.$el = $grid.find('table.grid thead th:not([style])').first();

                        var $input = $('<input type="checkbox">').css('margin', 0);
                        this.$el.empty().html($input);

                        $input.on('click', _.bind(function() {
                            var state = $input.is(':checked');
                            _.each(this.collection.models, function(model) {
                                model.set(this.columnName, state);
                            }, this);
                        }, this));
                    }
                }, this);

                mediator.bind('column_form_listener:set_selectors:' + this.gridName, function (selectors) {
                    this._clearState();
                    this.selectors = selectors;
                    this._restoreState();
                    this._synchronizeState();
                }, this);

                mediator.trigger('column_form_listener:initialized', this.gridName);
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
        }
    }
);
