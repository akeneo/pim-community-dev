import _ from 'underscore'
import BaseForm from 'pim/form'
import template from 'pim/template/attribute-option/form'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'

export default BaseForm.extend({
  template: _.template(template),
  events: {
    'change input': 'updateModel'
  },
  updateModel: function () {
    var optionValues = {}

    _.each(this.$('input[name^="label-"]'), function (labelInput) {
      var locale = labelInput.dataset.locale
      optionValues[locale] = {
        locale: locale,
        value: labelInput.value
      }
    })

    this.getFormModel().set('code', this.$('input[name="code"]').val())
    this.getFormModel().set('optionValues', optionValues)
  },
  render: function () {
    if (!this.configured) {
      return this
    }

    this.$el.html(
      this.template({
        locale: UserContext.get('catalogLocale'),
        i18n: i18n,
        option: this.getFormData()
      })
    )

    return this.renderExtensions()
  }
})
