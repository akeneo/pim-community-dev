
/**
 * Attributes used as label field view
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import FetcherRegistry from 'pim/fetcher-registry'
import i18n from 'pim/i18n'
import UserContext from 'pim/user-context'
import SecurityContext from 'pim/security-context'
import template from 'pim/template/family/tab/general/attribute-as-image'
import 'jquery.select2'
export default BaseForm.extend({
  className: 'AknFieldContainer',
  template: _.template(template),
  errors: [],
  catalogLocale: UserContext.get('catalogLocale'),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this
    }

    this.getAvailableAttributes().then(function (attributes) {
      this.$el.html(this.template({
        i18n: i18n,
        catalogLocale: this.catalogLocale,
        attributes: attributes,
        currentAttribute: this.getFormData().attribute_as_image,
        fieldBaseId: this.config.fieldBaseId,
        errors: this.errors,
        label: __(this.config.label),
        emptyLabel: __(this.config.emptyLabel),
        isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties')
      }))

      this.$('.select2').select2().on('change', this.updateState.bind(this))

      this.renderExtensions()
    }.bind(this))
  },

  /**
   * Update object state on property change
   *
   * @param event
   */
  updateState: function (event) {
    var data = this.getFormData()
    var value = event.currentTarget.value
    data.attribute_as_image = (value === 'no_attribute_as_image') ? null : event.currentTarget.value
    this.setData(data)
  },

  /**
   * Returns the list of available attributes for this extension:
   * - Should belong to the family
   * - Should be pim_catalog_image
   * - Should not be neither localizable nor scopable
   *
   * @returns {Promise}
   */
  getAvailableAttributes: function () {
    var imageAttributes = _.where(
      this.getFormData().attributes, {
        type: 'pim_catalog_image'
      }
    )

    var imageAttributeCodes = _.pluck(imageAttributes, 'code')

    return FetcherRegistry.getFetcher('attribute')
      .fetchByIdentifiers(imageAttributeCodes)
      .then(function (attributes) {
        return _.where(
          attributes, {
            scopable: false,
            localizable: false
          }
        )
      })
  }
})
