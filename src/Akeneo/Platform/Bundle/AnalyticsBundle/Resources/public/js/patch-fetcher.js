define(['jquery', 'pim/data-collector'], function ($, DataCollector) {
  'use strict';

  return {
    /**
     * @return {Promise}
     */
    fetch: function (updateServerUrl) {
      var storageEnabled = typeof Storage !== 'undefined' && sessionStorage;
      var lastPatchKey = 'last-patch-available';
      if (storageEnabled && null !== sessionStorage.getItem(lastPatchKey)) {
        var deferred = $.Deferred();
        deferred.resolve(sessionStorage.getItem(lastPatchKey));

        return deferred.promise();
      } else {
        return DataCollector.collect('pim_analytics_data_collect').then(function (collectedData) {
          var trackerVersion = '7.0';
          var page = collectedData.pim_edition + '-' + trackerVersion + '.json';
          var lastPatchUrl = updateServerUrl + '/' + page;

          return $.ajax({
            dataType: 'json',
            url: lastPatchUrl,
            data: collectedData,
            timeout: 10000,
          }).then(function (patchData) {
            var patch = patchData.last_patch.name;
            var cleanedPatch = patch.replace(/^v/g, '');
            sessionStorage.setItem(lastPatchKey, cleanedPatch);

            return cleanedPatch;
          });
        });
      }
    },
  };
});
