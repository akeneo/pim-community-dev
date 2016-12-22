/**
 * Extension to add a "remove" button on an optional filter.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry'

], function (_, __, BaseForm, fetcherRegistry) {
    return BaseForm.extend({
        /**
         * {@inherit}
         */
        initialize: function (config) {
            this.config = config.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:set-default', this.addFilter.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Adds filters to the collection.
         *
         * @param {Object} event
         */
        addFilter: function (event) {
            event.push(
                fetcherRegistry
                    .getFetcher('attribute')
                    .fetchByTypes(this.config.types)
                    .then(function (attributes) {
                        return _.pluck(attributes, 'code');
                    })
            );
        }
    });
});
