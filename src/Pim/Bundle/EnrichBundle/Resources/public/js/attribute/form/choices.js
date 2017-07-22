/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/common/form-container'

export default BaseForm.extend({
  className: 'AknTabContainer-content tab-content',
  template: _.template(template),
  config: {},

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
  configure: function () {
    if (_.contains(this.config.activeForTypes, this.getRoot().getType())) {
      this.trigger('tab:register', {
        code: this.code,
        label: __(this.config.label)
      })
    }

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!_.contains(this.config.activeForTypes, this.getRoot().getType())) {
      return
    }

    this.$el.html(this.template())

    this.renderExtensions()
  }
})
