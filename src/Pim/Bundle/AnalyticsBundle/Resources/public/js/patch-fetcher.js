define(
    ['jquery', 'underscore', 'pim/data-collector'],
    function ($, _, DataCollector) {
        'use strict';

        return {
            /**
             * @return {Object}
             */
            fetch: function (updateServerUrl) {
                return DataCollector.collect('pim_analytics_data_collect').then(function (collectedData) {
                    var version = collectedData['pim_version'];
                    var minorVersion = _.first(version.match(/^\d+.\d+/g));
                    var lastPatchUrl = updateServerUrl
                        + '/' + collectedData['pim_edition']
                        + '-' + minorVersion
                        + '.json';
                    // TODO: trim the "v" in the version code
                    return $.getJSON(lastPatchUrl, collectedData);
                });
            }
        };
    }
);
