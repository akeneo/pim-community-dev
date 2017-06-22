define(
    ['jquery', 'underscore', 'oro/mediator', 'oro/datafilter/select-filter', 'pim/user-context', 'pim/datagrid/state'],
    function ($, _, mediator, SelectFilter, UserContext, DatagridState) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/product_scope-filter
         * @class   oro.datafilter.ScopeFilter
         * @extends oro.datafilter.SelectFilter
         */
        return SelectFilter.extend({
            /**
             * @override
             * @property {Boolean}
             * @see Oro.Filter.SelectFilter
             */
            contextSearch: false,
            catalogScope: null,

            initialize: function() {
                SelectFilter.prototype.initialize.apply(this, arguments);
                this.catalogScope = UserContext.get('catalogScope');

                mediator.once('datagrid_filters:rendered', this.resetValue.bind(this));
                mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

                mediator.bind('grid_load:complete', function(collection) {
                    $('#grid-' + collection.inputName).find('div.toolbar').show();
                });
            },

            /**
             * Move the filter to its proper position
             *
             * @param {Array} collection
             */
            moveFilter: function (collection) {
                var $grid = $('#grid-' + collection.inputName);

                if (0 === $grid.length) {
                    $grid = $('[data-type="datagrid"]:first');
                }
                this.$el.addClass('AknFilterBox-filterContainer--inline').insertBefore($grid.find('.actions-panel'));

                var $filterChoices = $grid.find('#add-filter-select');
                $filterChoices.find('option[value="scope"]').remove();
                $filterChoices.multiselect('refresh');

                this.selectWidget.multiselect('refresh');
            },

            /**
             * Update the current filter value using the UserContext.
             */
            resetValue: function () {
                var scope = DatagridState.get('product-grid', 'scope');
                if (!scope) {
                    scope = this.catalogScope;
                }

                this.setValue({value: scope});
                UserContext.set('catalogScope', scope);
                this.selectWidget.multiselect('refresh');
            },

            /**
             * @inheritDoc
             */
            disable: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            hide: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function (newValue) {
                if ('' === newValue.value) {
                    return;
                }

                UserContext.set('catalogScope', newValue.value);

                return SelectFilter.prototype._onValueUpdated.apply(this, arguments);
            },

            /**
             * @inheritDoc
             *
             * Override to save the scope into the product grid state.
             *
             * We don't put this logic in the setValue method because we want this behavior only when the value
             * comes from a change of the select element, not from a view/url for example.
             */
            _onSelectChange: function() {
                SelectFilter.prototype._onSelectChange.apply(this, arguments);

                var value = this._formatRawValue(this._readDOMValue());
                DatagridState.set('product-grid', 'scope', value.value);
            },

            /**
             * Filter template
             *
             * @override
             * @property
             * @see Oro.Filter.SelectFilter
             */
            template: _.template(
                '<div class="AknFilterBox-filter filter-select filter-criteria-selector scope-filter">' +
                    '<i class="icon-eye-open" title="<%= label %>"></i>' +
                    '<select>' +
                        '<% _.each(options, function (option) { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>'
            )
        });
    }
);
