
/**
 * Content form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import template from 'pim/template/export/product/edit/content'
import BaseForm from 'pim/form'
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
  configure: function () {
    this.trigger('tab:register', {
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.tabTitle)
    })
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render.bind(this))

    return BaseForm.prototype.configure.apply(this, arguments)
  },

            /**
             * {@inheritdoc}
             */
  render: function () {
    if (!this.configured) {
      return this
    }

    this.$el.html(
                    this.template({})
                )

    this.renderExtensions()
  }
})
