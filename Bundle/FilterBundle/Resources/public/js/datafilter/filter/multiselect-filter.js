var Oro = Oro || {};
Oro.Filter = Oro.Filter || {};

/**
 * Multiple select filter: filter values as multiple select options
 *
 * @class   Oro.Filter.MultiSelectFilter
 * @extends Oro.Filter.SelectFilter
 */
Oro.Filter.MultiSelectFilter = Oro.Filter.SelectFilter.extend({
    /**
     * Multiselect filter template
     *
     * @property
     */
    template: _.template(
        '<div class="btn filter-select filter-criteria-selector">' +
            '<%= label %>: ' +
            '<select multiple>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
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
        Oro.Filter.SelectFilter.prototype._onSelectChange.apply(this, arguments);
        this._setDropdownWidth();
    }
});
