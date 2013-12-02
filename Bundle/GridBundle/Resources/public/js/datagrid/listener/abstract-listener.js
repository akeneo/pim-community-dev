/*jslint browser: true, vars: true, nomen: true*/
/*global define*/
define(['underscore', 'backbone', 'oro/grid/registry', 'oro/mediator'],
function(_, Backbone, registry, mediator) {
    'use strict';

    /**
     * Abstarct listener for datagrid
     *
     * @export  oro/grid/abstract-listener
     * @class   oro.grid.AbstractListener
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({

        /** @param {oro.grid.Grid} */
        datagrid: null,

        /** @param {String} Column name of cells that will be listened for changing their values */
        columnName: 'id',

        /** @param {String} Model field that contains data */
        dataField: 'id',

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function(options) {
            if (!_.has(options, 'columnName')) {
                throw new Error('Data column name is not specified');
            }
            this.columnName = options.columnName;

            if (options.dataField) {
                this.dataField = options.dataField;
            }

            Backbone.Model.prototype.initialize.apply(this, arguments);

            if (options.grid) {
                this.setDatagridAndSubscribe(options.grid);
            } else {
                if (!_.has(options, 'datagridName')) {
                    throw new Error('Datagrid name is not specified');
                }
                this._assignDatagridAndSubscribe(options.datagridName);
            }
        },

        /**
         * Subscribe to datagrid events
         *
         * @param {String} datagridName
         * @private
         */
        _assignDatagridAndSubscribe: function(datagridName) {
            var datagrid = registry.getElement('datagrid', datagridName);
            if (datagrid) {
                this.setDatagridAndSubscribe(datagrid);
            } else {
                mediator.once("datagrid:created:" + datagridName, this.setDatagridAndSubscribe, this);
            }
        },

        /**
         * Set datagrid instance
         *
         * @param {oro.grid.Grid} datagrid
         */
        setDatagridAndSubscribe: function(datagrid) {
            this.datagrid = datagrid;
            this.datagrid.collection.on('change:' + this.columnName, this._onModelEdited, this);
        },

        /**
         * Process cell editing
         *
         * @param {Backbone.Model} model
         * @param {Backgrid.Column} column
         * @protected
         */
        _onModelEdited: function (model) {
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
        _processValue: function(value, model) {
            throw new Error('_processValue method is abstract and must be implemented');
        }
    });
});
