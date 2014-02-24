define(
    ['underscore', 'oro/mediator', 'oro/datafilter/select-filter'],
    function (_, mediator, SelectFilter) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/scope-filter
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

            initialize: function(options) {
                SelectFilter.prototype.initialize.apply(this, arguments);

                mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

                mediator.bind('grid_load:complete', function(collection) {
                    $('#grid-' + collection.inputName).find('div.toolbar').show();
                });
            },

            moveFilter: function (collection) {
                var $grid = $('#grid-' + collection.inputName);
                this.$el.addClass('pull-right').insertBefore($grid.find('.actions-panel'));

                var $filterChoices = $grid.find('#add-filter-select');
                $filterChoices.find('option[value="scope"]').remove();
                $filterChoices.multiselect('refresh');

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
             * Filter template
             *
             * @override
             * @property
             * @see Oro.Filter.SelectFilter
             */
            template: _.template(
                '<div class="btn filter-select filter-criteria-selector scope-filter">' +
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
