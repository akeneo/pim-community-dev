/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/select-filter'],
function(_, __, SelectFilter) {
    'use strict';

    /**
     * Multiple select filter: filter values as multiple select options
     *
     * @export  oro/datafilter/multiselect-filter
     * @class   oro.datafilter.MultiSelectFilter
     * @extends oro.datafilter.SelectFilter
     */
    return SelectFilter.extend({
        /**
         * Multiselect filter template
         *
         * @property
         */
        template: _.template(
            '<div class="btn filter-select filter-criteria-selector">' +
                '<%= label %>: ' +
                '<select multiple>' +
                    '<% _.each(options, function (option) { %><option value="<%= option.value %>"><%= option.label %></option><% }); %>' +
                '</select>' +
            '</div>' +
            '<a href="<%= nullLink %>" class="disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a>'
        ),

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: true,
            classes: 'select-filter-widget multiselect-filter-widget'
        },

        /**
         * @inheritDoc
         */
        _onSelectChange: function() {
            SelectFilter.prototype._onSelectChange.apply(this, arguments);
            this._setDropdownWidth();
        }
    });
});