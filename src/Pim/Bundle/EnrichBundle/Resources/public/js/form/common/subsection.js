/**
 * Sub section extension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/form/subsection'

export default BaseForm.extend({
  className: 'AknSubsection',
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
    this.$el.empty().append(this.template({
      title: __(this.config.title)
    }))

    return BaseForm.prototype.render.apply(this, arguments)
  }
})
