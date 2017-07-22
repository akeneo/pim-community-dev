import $ from 'jquery'
import _ from 'underscore'
import DataCollector from 'pim/data-collector'

export default {
  /**
   * @return {Promise}
   */
  fetch: function (updateServerUrl) {
    var storageEnabled = typeof Storage !== 'undefined' && window.sessionStorage
    var lastPatchKey = 'last-patch-available'
    if (storageEnabled && window.sessionStorage.getItem(lastPatchKey) !== null) {
      var deferred = $.Deferred()
      deferred.resolve(window.sessionStorage.getItem(lastPatchKey))

      return deferred.promise()
    } else {
      return DataCollector.collect('pim_analytics_data_collect').then(function (collectedData) {
        var version = collectedData.pim_version
        var minorVersion = _.first(version.match(/^\d+.\d+/g))
        var page = collectedData.pim_edition + '-' + minorVersion + '.json'
        var lastPatchUrl = updateServerUrl + '/' + page

        return $.ajax({
          dataType: 'json',
          url: lastPatchUrl,
          data: collectedData,
          timeout: 10000
        }).then(function (patchData) {
          var patch = patchData.last_patch.name
          var cleanedPatch = patch.replace(/^v/g, '')
          window.sessionStorage.setItem(lastPatchKey, cleanedPatch)

          return cleanedPatch
        })
      })
    }
  }
}
