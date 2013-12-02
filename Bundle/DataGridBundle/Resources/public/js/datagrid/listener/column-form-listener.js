/*global define*/
define(['jquery', 'underscore', 'oro/translator', 'oro/mediator', 'oro/modal', 'oro/datagrid/abstract-listener'],
function($, _, __, mediator, Modal, AbstractListener) {
    'use strict';

    /**
     * Listener for entity edit form and datagrid
     *
     * @export  oro/datagrid/column-form-listener
     * @class   oro.datagrid.ColumnFormListener
     * @extends oro.datagrid.AbstractListener
     */
    var ColumnFormListener = AbstractListener.extend({

        /** @param {Object} */
        selectors: {
            included: null,
            excluded: null
        },

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function (options) {
            if (!_.has(options, 'selectors')) {
                throw new Error('Field selectors is not specified');
            }
            this.selectors = options.selectors;

            AbstractListener.prototype.initialize.apply(this, arguments);
        },

        /**
         * Set datagrid instance
         */
        setDatagridAndSubscribe: function () {
            AbstractListener.prototype.setDatagridAndSubscribe.apply(this, arguments);

            this.$gridContainer.on('preExecute:refresh:' + this.gridName, this._onExecuteRefreshAction.bind(this));
            this.$gridContainer.on('preExecute:reset:' + this.gridName, this._onExecuteResetAction.bind(this));

            this._clearState();
            this._restoreState();

            /**
             * Restore include/exclude state from pagestate
             */
            mediator.bind("pagestate_restored", function () {
                this._restoreState();
            }, this);
        },

        /**
         * Fills inputs referenced by selectors with ids need to be included and to excluded
         *
         * @param {*} id model id
         * @param {Backbone.Model} model
         * @protected
         */
        _processValue: function(id, model) {
            var original = this.get('original');
            var included = this.get('included');
            var excluded = this.get('excluded');

            var isActive = model.get(this.columnName);
            var originallyActive;
            if (_.has(original, id)) {
                originallyActive = original[id];
            } else {
                originallyActive = !isActive;
                original[id] = originallyActive;
            }

            if (isActive) {
                if (originallyActive) {
                    included = _.without(included, [id]);
                } else {
                    included = _.union(included, [id]);
                }
                excluded = _.without(excluded, id);
            } else {
                included = _.without(included, id);
                if (!originallyActive) {
                    excluded = _.without(excluded, [id]);
                } else {
                    excluded = _.union(excluded, [id]);
                }
            }

            this.set('included', included);
            this.set('excluded', excluded);
            this.set('original', original);

            this._synchronizeState();
        },

        /**
         * Clears state of include and exclude properties to empty values
         *
         * @private
         */
        _clearState: function () {
            this.set('included', []);
            this.set('excluded', []);
            this.set('original', {});
        },

        /**
         * Synchronize values of include and exclude properties with form fields and datagrid parameters
         *
         * @private
         */
        _synchronizeState: function () {
            var included = this.get('included');
            var excluded = this.get('excluded');
            if (this.selectors.included) {
                $(this.selectors.included).val(included.join(','));
            }
            if (this.selectors.excluded) {
                $(this.selectors.excluded).val(excluded.join(','));
            }
            mediator.trigger('datagrid:setParam:' + this.gridName, 'data_in', included);
            mediator.trigger('datagrid:setParam:' + this.gridName, 'data_not_in', excluded);
        },

        /**
         * Explode string into int array
         *
         * @param string
         * @return {Array}
         * @private
         */
        _explode: function(string) {
            if (!string) {
                return [];
            }
            return _.map(string.split(','), function(val) {return val ? parseInt(val, 10) : null});
        },

        /**
          * Restore values of include and exclude properties
          *
          * @private
          */
        _restoreState: function () {
            var included = '';
            var excluded = '';
            if (this.selectors.included && $(this.selectors.included).length) {
                included = this._explode($(this.selectors.included).val());
                this.set('included', included);
            }
            if (this.selectors.excluded && $(this.selectors.excluded).length) {
                excluded = this._explode($(this.selectors.excluded).val());
                this.set('excluded', excluded)
            }
            if (included || excluded) {
                mediator.trigger('datagrid:setParam:' + this.gridName, 'data_in', included);
                mediator.trigger('datagrid:setParam:' + this.gridName, 'data_not_in', excluded);
                mediator.trigger('datagrid:restoreState:' + this.gridName, this.columnName, this.dataField, included, excluded);
            }
         },

        /**
         * Confirms refresh action that before it will be executed
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} options
         * @private
         */
        _onExecuteRefreshAction: function (e, action, options) {
            this._confirmAction(action, options, 'refresh', {
                title: __('Refresh Confirmation'),
                content: __('Your local changes will be lost. Are you sure you want to refresh grid?')
            });
        },

        /**
         * Confirms reset action that before it will be executed
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} options
         * @private
         */
        _onExecuteResetAction: function(e, action, options) {
            this._confirmAction(action, options, 'reset', {
                title: __('Reset Confirmation'),
                content: __('Your local changes will be lost. Are you sure you want to reset grid?')
            });
        },

        /**
         * Asks user a confirmation if there are local changes, if user confirms then clears state and runs action
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} actionOptions
         * @param {String} type "reset" or "refresh"
         * @param {Object} confirmModalOptions Options for confirm dialog
         * @private
         */
        _confirmAction: function(action, actionOptions, type, confirmModalOptions) {
            this.confirmed = this.confirmed || {};
            if (!this.confirmed[type] && this._hasChanges()) {
                actionOptions.doExecute = false; // do not execute action until it's confirmed
                this._openConfirmDialog(type, confirmModalOptions, function () {
                    // If confirmed, clear state and run action
                    this.confirmed[type] = true;
                    this._clearState();
                    this._synchronizeState();
                    action.run();
                });
            }
            this.confirmed[type] = false;
        },

        /**
         * Returns TRUE if listener contains user changes
         *
         * @return {Boolean}
         * @private
         */
        _hasChanges: function() {
            return !_.isEmpty(this.get('included')) || !_.isEmpty(this.get('excluded'));
        },

        /**
         * Opens confirm modal dialog
         */
        _openConfirmDialog: function(type, options, callback) {
            this.confirmModal = this.confirmModal || {};
            if (!this.confirmModal[type]) {
                this.confirmModal[type] = new Modal(_.extend({
                    title: __('Confirmation'),
                    okText: __('Ok, got it.'),
                    className: 'modal modal-primary',
                    okButtonClass: 'btn-primary btn-large'
                }, options));
                this.confirmModal[type].on('ok', _.bind(callback, this));
            }
            this.confirmModal[type].open();
        }
    });

    ColumnFormListener.init = function ($gridContainer, gridName) {
        var metadata = $gridContainer.data('metadata');
        var options = metadata.options || {};
        if (options.columnListener) {
            new ColumnFormListener(_.extend({ $gridContainer: $gridContainer, gridName: gridName }, options.columnListener));
        }
    };

    return ColumnFormListener;
});
