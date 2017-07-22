
/**
 * Index extension for any basic screen with grid
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/form/index/index'
import formBuilder from 'pim/form-builder'
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
    this.$el.html(this.template({
      title: __(this.config.title)
    }))

    this.renderExtensions()

    formBuilder.buildForm('pim-menu-user-navigation').then(function (form) {
      form.setElement('.user-menu').render()
    })

    return this
  }
})
