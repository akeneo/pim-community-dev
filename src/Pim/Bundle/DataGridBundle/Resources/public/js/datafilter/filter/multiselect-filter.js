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
            '<div class="AknActionButton filter-select filter-criteria-selector">' +
                '<% if (showLabel) { %><%= label %>: <% } %>' +
                '<select multiple>' +
                    '<% _.each(options, function (option) { %>' +
                        '<% if(_.isObject(option.value)) { %>' +
                            '<optgroup label="<%= option.label %>">' +
                                '<% _.each(option.value, function (value) { %>' +
                                    '<option value="<%= value.value %>"><%= value.label %></option>' +
                                '<% }); %>' +
                            '</optgroup>' +
                        '<% } else { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% } %>' +
                    '<% }); %>' +
                '</select>' +
            '</div>' +
            '<% if (canDisable) { %><a href="<%= nullLink %>" class="AknFilterBox-disableFilter disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a><% } %>'
        ),

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: true,
            classes: 'AknActionButton-selectButton select-filter-widget multiselect-filter-widget'
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
