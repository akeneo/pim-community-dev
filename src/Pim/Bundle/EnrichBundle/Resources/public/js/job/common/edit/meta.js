/**
 * Label extension for jobs
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import BaseForm from 'pim/form'
import _ from 'underscore'
import __ from 'oro/translator'
import template from 'pim/template/export/common/edit/meta'

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template({
      jobInstance: this.getFormData(),
      __: __
    }))

    return this
  }
})
