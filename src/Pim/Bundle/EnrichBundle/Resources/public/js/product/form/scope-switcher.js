
/**
 * Scope switcher extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import template from 'pim/template/product/scope-switcher'
import FetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
export default BaseForm.extend({
  template: _.template(template),
  className: 'AknDropdown AknButtonList-item scope-switcher',
  events: {
    'click li a': 'changeScope'
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
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
      if (localeEvent.context === 'base_product') {
        UserContext.set('catalogLocale', localeEvent.localeCode)
        this.render()
      }
    }.bind(this))

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    FetcherRegistry.getFetcher('channel')
      .fetchAll()
      .then(function (channels) {
        const params = {
          scopeCode: channels[0].code,
          context: this.config.context
        }
        this.getRoot().trigger('pim_enrich:form:scope_switcher:pre_render', params)

        var scope = _.findWhere(channels, {
          code: params.scopeCode
        })

        this.$el.html(
          this.template({
            channels: channels,
            currentScope: i18n.getLabel(
              scope.labels,
              UserContext.get('catalogLocale'),
              scope.code
            ),
            catalogLocale: UserContext.get('catalogLocale'),
            i18n: i18n,
            displayInline: this.displayInline,
            label: __('pim_enrich.entity.product.meta.scope')
          })
        )

        this.delegateEvents()
      }.bind(this))

    return this
  },

  /**
   * Set the current selected scope
   *
   * @param {Event} event
   */
  changeScope: function (event) {
    this.getRoot().trigger('pim_enrich:form:scope_switcher:change', {
      scopeCode: event.currentTarget.dataset.scope,
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
