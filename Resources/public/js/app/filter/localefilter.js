var Pim = Pim || {};
Pim.Filter = Pim.Filter || {};

/**
 * Locale filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @class     Pim.Filter.LocaleFilter
 * @extends   Oro.Filter.SelectFilter
 */
Pim.Filter.LocaleFilter = Oro.Filter.SelectFilter.extend({

    /**
     * @property {Boolean}
     */
    contextSearch: false,
    
    /**
     * Filter template
     *
     * @see Oro.Filter.SelectFilter
     * @override
     * @property
     */
    template: _.template(
        '<div class="btn filter-select filter-criteria-selector">' +
            '<%= label %>: ' +
            '<select>' +
                '<% _.each(options, function (hint, value) { %><option value="<%= value %>"><%= hint %></option><% }); %>' +
            '</select>' +
        '</div>'
    ),
});