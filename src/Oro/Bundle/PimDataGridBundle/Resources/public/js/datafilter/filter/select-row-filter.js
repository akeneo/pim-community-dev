/* global define */
define(['underscore', 'backbone', 'oro/datafilter/select-filter'],
function(_, Backbone, SelectFilter) {
    'use strict';

    /**
     * Fetches information of rows selection
     * and implements filter by selected/Not selected rows
     *
     * @export  oro/datafilter/select-row-filter
     * @class   oro.datafilter.SelectRowFilter
     * @extends oro.datafilter.SelectFilter
     */
    return SelectFilter.extend({

        /**
         * Fetches raw format value on getting current value
         * in order to give always actual information about selected rows
         *
         * @return {Object}
         */
        getValue: function() {
            return this._formatRawValue(_.omit(this.value, 'in', 'out'));
        },

        /**
         * Converts a display value into raw format. Adds to value 'in' or 'out' property
         * with comma-separated string of ids, e.g. {'in': '4,35,23,65'} or {'out': '7,31,63,12'}
         *
         * @param {Object} value
         * @return {Object}
         * @protected
         */
        _formatRawValue: function(value) {
            // if a display value already contains raw information assume it's an initialization
            if (_.has(value, 'in') || _.has(value, 'out')) {
                this._initialSelection(value);
            }
            if (value.value !== '') {
                var ids = this._getSelection(),
                    scope;
                if (_.isArray(ids.selected)) {
                    scope = (ids.inset === Boolean(parseInt(value.value, 10)) ? 'in' : 'out');
                    value[scope] = ids.selected.join(',');
                }
            }
            return value;
        },

        /**
         * Converts a raw value into display format, opposite to _formatRawValue.
         * Removes extra properties of raw value representation.
         *
         * @param {Object} value
         * @return {Object}
         * @protected
         */
        _formatDisplayValue: function(value) {
            return _.omit(value, 'in', 'out');
        },

        /**
         * Fetches selection of grid rows
         * Triggers an event 'backgrid:getSelected' on collection to get selected rows.
         * oro.datagrid.SelectAllHeaderCell is listening to this event and
         * fills in a passes flat object with selection information
         *
         * @returns {Object}
         * @protected
         */
        _getSelection: function () {
            var selection = {};
            this.collection.trigger('backgrid:getSelected', selection);
            return _.defaults(selection, {inset : true, selected : []});
        },

        /**
         * Triggers selection events for models on grid's initial stage
         * (if display value has raw data, it's initial stage)
         *
         * @param {Object} value
         * @param {string} value.value "0" - not selected, "1" - selected
         * @param {string} value.in comma-separated ids
         * @param {string} value.out comma-separated ids
         * @protected
         */
        _initialSelection: function(value) {
            var checked = true;
            if (Boolean(parseInt(value.value, 10)) !== _.has(value, 'in')) {
                this.collection.trigger('backgrid:selectAll');
                checked = false;
            }
            _.each(
                _.values(_.pick(value, 'in', 'out'))[0].split(',') || [],
                _.partial(function(collection, id) {
                    var model = collection.get(id);
                    if (model instanceof Backbone.Model) {
                        model.trigger("backgrid:select", model, checked);
                    }
                }, this.collection)
            );
        }
    });
});
