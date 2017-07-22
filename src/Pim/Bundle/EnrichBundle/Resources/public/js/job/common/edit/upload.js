/**
 * Displays a drop zone to upload a file.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseForm from 'pim/form'
import template from 'pim/template/export/common/edit/upload'

export default BaseForm.extend({
  template: _.template(template),
  events: {
    'change input[type="file"]': 'addFile',
    'click .clear-field': 'removeFile'
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template({
      file: this.getFormData().file
    }))

    this.delegateEvents()

    return this
  },

  /**
   * When a file is added to the dom input
   */
  addFile: function () {
    var input = this.$('input[type="file"]').get(0)
    if (!input || input.files.length === 0) {
      return
    }

    this.setData({
      file: input.files[0]
    })

    this.getRoot().trigger('pim_enrich:form:job:file_updated')

    this.render()
  },

  /**
   * When the user remove the file from the input
   */
  removeFile: function () {
    this.setData({
      file: null
    })

    this.getRoot().trigger('pim_enrich:form:job:file_updated')

    this.render()
  }
})
