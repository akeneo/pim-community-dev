/**
 * Code field view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/form/properties/input'

export default BaseForm.extend({
  className: 'input',
  template: _.template(template),
  errors: [],

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this
    }

    this.$el.html(this.template({
      fieldName: this.config.fieldBaseId + 'code',
      className: 'family-code',
      value: this.getFormData().code,
      errors: [],
      label: __(this.config.label),
      requiredLabel: __('pim_enrich.form.required'),
      isRequired: true,
      isReadOnly: true
    }))

    this.renderExtensions()
  }
})
