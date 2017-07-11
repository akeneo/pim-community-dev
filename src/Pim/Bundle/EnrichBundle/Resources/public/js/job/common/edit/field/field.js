
/**
 * Base field form extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import propertyAccessor from 'pim/common/property'
import template from 'pim/template/export/common/edit/field/field'
export default BaseForm.extend({
  template: _.template(template),

        /**
         * {@inheritdoc}
         */
  initialize: function (config) {
    this.config = config.config

    BaseForm.prototype.initialize.apply(this, arguments)
  },

        /**
         * {@inheritdoc}
         */
  render: function () {
    this.$el.html(
                this.template(this.getTemplateContext())
            )

    this.$('.field-input').prepend(this.renderInput(this.getTemplateContext()))
    this.$('[data-toggle="tooltip"]').tooltip()

    this.delegateEvents()

    return this
  },

        /**
         * Render the input itself
         *
         * @param {object} templateContext
         *
         * @return {string}
         */
  renderInput: function (templateContext) {
    return this.fieldTemplate(templateContext)
  },

        /**
         * Get the template object for the field
         *
         * @return {object}
         */
  getTemplateContext: function () {
    return {
      __: __,
      value: this.getValue(),
      config: this.config,
      error: this.getParent().getValidationErrorsForField(this.getFieldCode())
    }
  },

        /**
         * Get the current value of the field
         *
         * @return {mixed}
         */
  getValue: function () {
    return propertyAccessor.accessProperty(this.getFormData(), this.getFieldCode())
  },

        /**
         * Get the field code of the property
         *
         * @return {strign}
         */
  getFieldCode: function () {
    return this.config.fieldCode
  },

        /**
         * Update the model after dom update
         */
  updateState: function () {
    var data = propertyAccessor.updateProperty(this.getFormData(), this.getFieldCode(), this.getFieldValue())

    this.setData(data)
  }
})
