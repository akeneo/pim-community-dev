import $ from 'jquery'
import DialogForm from 'pim/dialogform'
import messenger from 'oro/messenger'
import initSelect2 from 'pim/initselect2'
import 'jquery.select2'

var init = function (fieldId) {
  var $field = $(fieldId)
  var $target = $field.parent().find('.icons-container').first()
  if ($target.length) {
    $field.insertBefore($target).attr('tabIndex', -1)
  }
  var callback = function (data) {
    if (data.status) {
      var $select = $field.siblings('input.pim-ajax-entity')
      var selectData = {
        id: data.option.id,
        text: data.option.label
      }
      if ($select.attr('data-multiple')) {
        selectData = (function (newElement) {
          var selectData = $select.select2('data')
          selectData.push(newElement)

          return selectData
        })(selectData)
      }
      $select.select2('destroy')
      initSelect2.initSelect($select)
      $select.trigger('change')
      $select.select2('data', selectData)
      messenger.notify('success', $field.data('success-message'))
    } else {
      messenger.notify('error', $field.data('error-message'))
    }
  }
  new DialogForm(fieldId, callback)
}

export default {
  init: init
}
