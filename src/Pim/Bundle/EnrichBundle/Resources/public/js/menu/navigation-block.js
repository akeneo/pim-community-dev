/**
 * Base extension for navigation blocks
 * A navigation block is composed of a title and a list of items, displayed in the columns.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/menu/navigation-block'

export default BaseForm.extend({
  className: 'AknColumn-block',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config

    BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * Proxy for 'pim_menu:column:register_navigation_item' event
   *
   * {@inheritdoc}
   */
  configure: function () {
    this.onExtensions('pim_menu:column:register_navigation_item', function (event) {
      this.trigger('pim_menu:column:register_navigation_item', event)
    })

    BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(this.template({
      title: __(this.config.title)
    }))

    BaseForm.prototype.render.apply(this, arguments)
  }
})
