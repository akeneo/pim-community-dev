/* global define */
define(['oro/datagrid/mass-action'],
function(MassAction) {
    'use strict';

    /**
     * Override abstract action to add the datalocale parameter
     */
    return MassAction.extend({
        /**
         * Get action parameters
         *
         * @returns {Object}
         * @private
         */
        getActionParameters: function() {
            var params = MassAction.prototype.getActionParameters();
            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();
            if (locale) {
                params['dataLocale']= locale;
            }

            return params;
        }

    });
});
