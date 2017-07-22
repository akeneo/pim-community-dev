import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import 'bootstrap.datetimepicker'

export default {
  options: {
    language: 'en',
    pickTime: false
  },
  init: function ($target, options) {
    options = $.extend(true, {}, this.options, options)

    if ((options.language !== 'en') && (undefined === $.fn.datetimepicker.dates[options.language])) {
      var languageOptions = {}
      var defaultOptions = $.fn.datetimepicker.dates.en

      _.each(_.keys(defaultOptions), function (key) {
        languageOptions[key] = []
        _.each(defaultOptions[key], function (value) {
          languageOptions[key].push(__('datetimepicker.' + key + '.' + value))
        })
      })

      $.fn.datetimepicker.dates[options.language] = languageOptions
    }

    $target.datetimepicker(options)

    return $target
  }
}
