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

define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'oro/datafilter/select-filter',
        'pim/user-context',
        'pim/datagrid/state',
        'pim/template/datagrid/filter/scope-filter'
    ],
    function (
        $,
        _,
        mediator,
        SelectFilter,
        UserContext,
        DatagridState,
        template
    ) {
        'use strict';

        return SelectFilter.extend({
            template: _.template(template),
            className: 'AknDropdown AknColumn-block filter-item',
            events: {
                'keydown select': '_preventEnterProcessing',
                'click .filter-select': '_onClickFilterArea',
                'click .disable-filter': '_onClickDisableFilter',
                'click .AknDropdown-menuLink': '_onSelectChange'
            },
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
                this.$el.appendTo($('[data-drop-zone="product-scope-filter"]'));

                let $grid = $('#grid-' + collection.inputName);

                if (0 === $grid.length) {
                    $grid = $('[data-type="datagrid"]:first');
                }

                let $filterChoices = $grid.find('#add-filter-select');
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

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                SelectFilter.prototype.render.apply(this, arguments);

                this.$el.find('.value').html(_.findWhere(this.options.choices, {value: this.catalogScope}).label);
                this.$el.find('.AknDropdown-menuLink').removeClass('.AknDropdown-menuLink--active')
                this.$el.find('.AknDropdown-menuLink[data-value="' + this.catalogScope + '"]')
                    .addClass('AknDropdown-menuLink--active');
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
                const value = $(event.target).closest('.AknDropdown-menuLink').attr('data-value');
                this.catalogScope = value;
                this.setValue(value);
                DatagridState.set('product-grid', 'scope', value);

                this.render();
            },
        });
    }
);
