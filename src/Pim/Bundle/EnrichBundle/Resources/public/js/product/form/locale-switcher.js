/**
 * Locale switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/product/locale-switcher'
import FetcherRegistry from 'pim/fetcher-registry'
import i18n from 'pim/i18n'

export default BaseForm.extend({
  template: _.template(template),
  className: 'AknDropdown AknButtonList-item locale-switcher',
  events: {
    'click li a': 'changeLocale'
  },
  displayInline: false,
  config: {},

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    if (undefined !== config) {
      this.config = config.config
    }

    BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.getDisplayedLocales()
      .done(function (locales) {
        const params = {
          localeCode: _.first(locales).code,
          context: this.config.context
        }
        this.getRoot().trigger('pim_enrich:form:locale_switcher:pre_render', params)

        this.$el.html(
          this.template({
            locales: locales,
            currentLocale: _.findWhere(locales, {
              code: params.localeCode
            }),
            i18n: i18n,
            displayInline: this.displayInline,
            label: __('pim_enrich.entity.product.meta.locale')
          })
        )
        this.delegateEvents()
      }.bind(this))

    return this
  },

  /**
   * Retrieve locales to display in the locale switcher
   *
   * @returns {Promise}
   */
  getDisplayedLocales: function () {
    return FetcherRegistry.getFetcher('locale').fetchActivated()
  },

  /**
   * Method triggered on the 'change locale' event
   *
   * @param {Object} event
   */
  changeLocale: function (event) {
    this.getRoot().trigger('pim_enrich:form:locale_switcher:change', {
      localeCode: event.currentTarget.dataset.locale,
      context: this.config.context
    })

    this.render()
  },

  /**
   * Updates the inline display value
   *
   * @param {Boolean} value
   */
  setDisplayInline: function (value) {
    this.displayInline = value
  }
})
