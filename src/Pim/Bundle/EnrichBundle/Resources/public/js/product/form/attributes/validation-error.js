
/**
 * Validation error extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import Backbone from 'backbone'
import template from 'pim/template/product/tab/attribute/validation-error'
import i18n from 'pim/i18n'
export default Backbone.View.extend({
  template: _.template(template),
  className: 'AknFieldContainer-validationErrors validation-errors',
  events: {
    'click .change-context': 'changeContext'
  },
  initialize: function (errors, parent) {
    this.errors = errors
    this.parent = parent
  },
  render: function () {
    this.$el.html(this.template({errors: this.errors, i18n: i18n}))
    this.delegateEvents()

    return this
  },
  changeContext: function (event) {
    this.parent.changeContext(event.currentTarget.dataset.locale, event.currentTarget.dataset.scope)
  }
})
