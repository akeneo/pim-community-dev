

import $ from 'jquery';
import Routing from 'routing';
        export default {
            /**
             * Save the given datagridView for the given gridAlias.
             * Return the POST request promise.
             *
             * @param {object} datagridView
             * @param {string} gridAlias
             *
             * @returns {Promise}
             */
            save: function (datagridView, gridAlias) {
                var saveRoute = Routing.generate(__moduleConfig.url, {alias: gridAlias});

                return $.post(saveRoute, {view: datagridView});
            }
        };
    
