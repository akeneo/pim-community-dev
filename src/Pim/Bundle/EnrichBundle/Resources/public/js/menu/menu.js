/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseForm from 'pim/form'
import template from 'pim/template/menu/menu'

export default BaseForm.extend({
  className: 'AknHeader',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(this.template())

    return BaseForm.prototype.render.apply(this, arguments)
  }
})
