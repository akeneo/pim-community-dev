/* global define */
define(['underscore', 'oro/messenger', 'oro/translator', 'pim/dialog', 'oro/datagrid/abstract-action'],
function(_, messenger, __, Dialog, AbstractAction) {
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
            confirm_title: __('pim_datagrid.mass_action.default.confirmation.title'),
            confirm_content: __('pim_datagrid.mass_action.default.confirmation.content'),
            confirm_ok: __('pim_common.yes'),
            success: __('pim_datagrid.mass_action.default.success'),
            error: __('pim_datagrid.mass_action.default.error'),
            empty_selection: __('pim_datagrid.mass_action.default.no_items')
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
                messenger.notify('warning', this.messages.empty_selection);
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
                values: idValues.join(',')
            };

            if (selectionState.inset) {
                params.itemsCount = idValues.length;
            } else {
                params.itemsCount = collection.state.totalRecords - idValues.length;
            }

            params = this.getExtraParameters(params, collection.state);

            params = collection.processFiltersParams(params, null, 'filters');

            var locale = this.getLocaleFromUrl('dataLocale');

            if ('family-grid' === this.datagrid.name) {
                locale = this.getLocaleFromUrl('localeCode');
                delete params['filters[label][value]'];
            }

            if (locale) {
                params.dataLocale = locale;
            }

            return params;
        },

        /**
         * Get the locale from the datagrid collection url with a given key
         * @param  {String} localeKey For example dataLocale or localeCode
         * @return {String} locale.   The returned locale e.g. en_US
         */
        getLocaleFromUrl: function(localeKey) {
            const url = this.datagrid.collection.url.split('?')[1];
            const urlParams = this.datagrid.collection.decodeStateData(url);
            const datagridParams = urlParams[this.datagrid.name] || {};

            return urlParams[localeKey] || datagridParams[localeKey];
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

        _onAjaxSuccess: function(data, textStatus, jqXHR) {
            this.datagrid.resetSelectionState();
            AbstractAction.prototype._onAjaxSuccess.apply(this, arguments);
        },

        /**
         * Get view for confirm modal
         *
         * @return {oro.Modal}
         */
        getConfirmDialog: function(callback) {
            return Dialog.confirm(
              this.messages.confirm_content,
              this.messages.confirm_title,
              callback,
              this.getEntityHint(true),
              `${this.className} ok`,
              this.messages.confirm_ok,
              this.type
            );
        }
    });
});
