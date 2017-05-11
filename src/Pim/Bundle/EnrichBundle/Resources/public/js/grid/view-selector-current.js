'use strict';

/**
 * Module to display the current view in the Datagrid View Selector.
 * This module accepts extensions to display more info beside the view.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/template/grid/view-selector/current'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            datagridView: null,
            dirtyColumns: false,
            dirtyFilters: false,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'grid:view-selector:state-changed',
                    this.onDatagridStateChange.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    view: this.datagridView,
                    dirtyFilters: this.dirtyFilters,
                    dirtyColumns: this.dirtyColumns
                }));

                this.renderExtensions();

                return this;
            },

            /**
             * Method called on datagrid state change (when columns or filters are modified).
             * Set the state to dirty if it's the case then re-render this extension.
             *
             * @param {Object} datagridState
             */
            onDatagridStateChange: function (datagridState) {
                if (null === datagridState.columns) {
                    datagridState.columns = '';
                }

                var initialView = this.getRoot().initialView;
                var initialViewExists = null !== initialView && 0 !== initialView.id;

                var filtersModified = this.areFiltersModified(initialView.filters, datagridState.filters);
                var columnsModified = !_.isEqual(initialView.columns, datagridState.columns.split(','));

                if (initialViewExists) {
                    this.dirtyFilters = filtersModified;
                    this.dirtyColumns = columnsModified;
                } else {
                    var isDefaultFilters = ('' === datagridState.filters);
                    var isDefaultColumns = _.isEqual(this.getRoot().defaultColumns, datagridState.columns.split(','));

                    this.dirtyFilters = !isDefaultFilters;
                    this.dirtyColumns = !isDefaultColumns;
                }

                this.render();
            },

            /**
             * Set the view of this module.
             *
             * @param {Object} view
             */
            setView: function (view) {
                this.datagridView = view;
            },

            /**
             * Check if current datagrid state filters are modified regarding the initial view
             *
             * @param {Object} initialViewFilters
             * @param {Object} datagridStateFilters
             *
             * @return {boolean}
             */
            areFiltersModified: function (initialViewFilters, datagridStateFilters) {
                return initialViewFilters !== datagridStateFilters;
            }
        });
    }
);
