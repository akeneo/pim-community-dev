/*jslint browser: true, nomen: true*/
/*global define*/
define(['underscore', 'jquery', 'backbone'], function (_, $, Backbone) {
    'use strict';

    /**
     * Abstarct listener for datagrid
     *
     * @export  oro/datagrid/abstract-listener
     * @class   oro.datagrid.AbstractListener
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        /** @param {String} Column name of cells that will be listened for changing their values */
        columnName: 'id',

        /** @param {String} Model field that contains data */
        dataField: 'id',

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function (options) {
            if (!_.has(options, 'columnName')) {
                throw new Error('Data column name is not specified');
            }
            this.columnName = options.columnName;

            if (options.dataField) {
                this.dataField = options.dataField;
            }

            Backbone.Model.prototype.initialize.apply(this, arguments);

            if (!options.$gridContainer) {
                throw new Error('gridSelector is not specified');
            }
            this.$gridContainer = options.$gridContainer;
            this.gridName = options.gridName;

            this.setDatagridAndSubscribe();
        },

        /**
         * Set datagrid instance
         */
        setDatagridAndSubscribe: function () {
            this.$gridContainer.on('datagrid:change:' + this.gridName, this._onModelEdited.bind(this));
        },

        /**
         * Process cell editing
         *
         * @param {Backbone.Model} model
         * @protected
         */
        _onModelEdited: function (e, model) {
            if (!model.hasChanged(this.columnName)) {
                return;
            }

            var value = model.get(this.dataField);

            if (!_.isUndefined(value)) {
                this._processValue(value, model);
            }
        },

        /**
         * Process value
         *
         * @param {*} value Value of model property with name of this.dataField
         * @param {Backbone.Model} model
         * @protected
         * @abstract
         */
        _processValue: function (value, model) {
            throw new Error('_processValue method is abstract and must be implemented');
        }
    });
});
