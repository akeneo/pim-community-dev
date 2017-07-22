/**
 * Create button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/form/index/create-button'
import Routing from 'routing'
import DialogForm from 'pim/dialogform'
import FormBuilder from 'pim/form-builder'

export default BaseForm.extend({
  template: _.template(template),
  dialog: null,

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
      title: __(this.config.title),
      iconName: this.config.iconName,
      url: this.config.url ? Routing.generate(this.config.url) : ''
    }))

    if (this.config.modalForm) {
      this.$el.on('click', function () {
        FormBuilder.build(this.config.modalForm)
          .then(function (modal) {
            modal.open()
          })
      }.bind(this))

      return this
    }

    // TODO-Remove the following line when all entities will be managed (TIP-730 completed)
    this.dialog = new DialogForm('#create-button-extension')

    return this
  }
})
