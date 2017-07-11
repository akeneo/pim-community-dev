
/**
 * Attributes tab top toolbar view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/family/tab/attributes/toolbar'
export default BaseForm.extend({
  className: 'AknGridToolbar',
  template: _.template(template),
  errors: [],

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
    if (!this.configured) {
      return this
    }

    this.$el.html(this.template({}))

    this.renderExtensions()
  }
})
