/**
 * Textarea field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import Field from 'pim/field'
import _ from 'underscore'
import fieldTemplate from 'pim/template/product/field/textarea'

export default Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first textarea': 'updateModel'
  },

  /**
   * @inheritDoc
   */
  renderInput: function (context) {
    return this.fieldTemplate(context)
  },

  /**
   * @inheritDoc
   */
  updateModel: function () {
    var data = this.$('.field-input:first textarea:first').val()
    data = data === '' ? this.attribute.empty_value : data

    this.setCurrentValue(data)
  },

  /**
   * @inheritDoc
   */
  setFocus: function () {
    this.$('.field-input:first textarea').focus()
  }
})
