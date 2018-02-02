/* global define */
define(['underscore', 'oro/messenger', 'oro/translator', 'oro/modal', 'oro/datagrid/abstract-action'],
function(_, messenger, __, Modal, AbstractAction) {
    'use strict';

    /**
     * Basic mass action class.
     *
     * @export  oro/datagrid/mass-action
     * @class   oro.datagrid.MassAction
     * @extends oro.datagrid.AbstractAction
     */
    return AbstractAction.extend({
        /** @property {Object} */
        defaultMessages: {
            confirm_title: __('Mass Action Confirmation'),
            confirm_content: __('Are you sure you want to do this?'),
            confirm_ok: __('Yes, do it'),
            success: __('Mass action performed.'),
            error: __('Mass action is not performed.'),
            empty_selection: __('Please, select items to perform mass action.')
        },

        initialize: function(options) {
            AbstractAction.prototype.initialize.apply(this, arguments);
            this.route_parameters = _.extend(this.route_parameters, {gridName: this.datagrid.name, actionName: this.name});

            _.defaults(this.messages, this.defaultMessages);
        },

        /**
         * Ask a confirmation and execute mass action.
         */
        execute: function() {
            var selectionState = this.datagrid.getSelectionState();
            if (_.isEmpty(selectionState.selectedModels) && selectionState.inset) {
                messenger.notificationFlashMessage('warning', this.messages.empty_selection);
            } else {
                AbstractAction.prototype.execute.call(this);
            }
        },

        /**
         * Get action parameters
         *
         * @returns {Object}
         * @private
         */
        getActionParameters: function() {
            var selectionState = this.datagrid.getSelectionState();
            var collection     = this.datagrid.collection;

            var idValues = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName);
            }, this);
            var params = {
                inset: selectionState.inset ? 1 : 0,
            };

            if (selectionState.inset) {
                params.itemsCount = idValues.length;
            } else {
                params.itemsCount = collection.state.totalRecords - idValues.length;
            }

            params = this.getExtraParameters(params, collection.state);

            params = collection.processFiltersParams(params, null, 'filters');

            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();

            if ('family-grid' === this.datagrid.name) {
                locale = decodeURIComponent(this.datagrid.collection.url).split('localeCode]=').pop();
            }

            if (locale) {
                params.dataLocale = locale;
            }

            return params;
        },

        getSelectedRows: function() {
            var selectionState = this.datagrid.getSelectionState();
            var itemIds = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName);
            }, this);

            return itemIds;
        },

        _handleAjax: function(action) {
            if (action.dispatched) {
                return;
            }
            action.datagrid.showLoading();
            $.post(action.getLinkWithParameters(), {itemIds: action.getSelectedRows().join(',')})
                .done(this._onAjaxSuccess.bind(this))
                .fail(this._onAjaxError.bind(this));
        },

        /**
         * Get extra parameters (sorters and custom parameters)
         * @param {array}  params
         * @param {object} state
         *
         * @return {object}
         */
        getExtraParameters: function(params, state) {
            params[this.datagrid.name] = {};

            if (state !== undefined) {
                params[this.datagrid.name]._parameters = this.getActiveSorters(state);
                params[this.datagrid.name]._sort_by    = this.getActiveColumns(state);
            }

            return params;
        },

        /**
         * Get active sorters
         * @param {object} state
         *
         * @return {object}
         */
        getActiveSorters: function(state) {
            var result = {};

            if (state.parameters !== undefined && state.parameters.view !== undefined) {
                result.view = {
                    columns: state.parameters.view.columns
                };
            }

            return result;
        },

        /**
         * Get active columns
         * @param {object} state
         *
         * @return {object}
         */
        getActiveColumns: function(state) {
            var result = {};

            if (state.sorters !== undefined) {
                for (var sorterKey in state.sorters) {
                    result[sorterKey] = state.sorters[sorterKey] === 1 ? 'DESC' : 'ASC';
                }
            }

            return result;
        },

        /**
         * Get view for confirm modal
         *
         * @return {oro.Modal}
         */
        getConfirmDialog: function(callback) {
            return new Modal({
                title: this.messages.confirm_title,
                content: this.messages.confirm_content,
                okText: this.messages.confirm_ok
            }).on('ok', callback);
        },

        saveItemIds: function() {
            localStorage.setItem('mass_action.itemIds', JSON.stringify(this.getSelectedRows()));
        }
    });
});
