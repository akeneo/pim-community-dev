var Pim = Pim || {};
Pim.Filter = Pim.Filter || {};

/**
 * Completeness filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @class     Pim.Filter.CompletenessFilter
 * @extends   Oro.Filter.SelectFilter
 */
Pim.Filter.CompletenessFilter = Oro.Filter.SelectFilter.extend({
    /**
     * @override
     * @property {Boolean}
     * @see Oro.Filter.SelectFilter
     */
    contextSearch: false,

    /**
     * @inheritDoc
     */
    disable: function() {
        return this;
    },

    /**
     * @inheritDoc
     */
    hide: function() {
        return this;
    },
    
    placeholder: '',
    
    template: _.template(
        '<div class="btn filter-select filter-criteria-selector">' +
            '<%= label %>: ' +
            '<select>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
        '</div>' +
        '<a href="<%= nullLink %>" class="disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a>'
    ),
});
