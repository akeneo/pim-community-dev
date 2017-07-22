/**
 * Family select2 to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import Routing from 'routing'
import BaseForm from 'pim/form'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import __ from 'oro/translator'
import FetcherRegistry from 'pim/fetcher-registry'
import initSelect2 from 'pim/initselect2'
import template from 'pim/template/form/creation/family'
import 'jquery.select2'

export default BaseForm.extend({
  template: _.template(template),
  validationErrors: {},
  defaultIdentifier: 'family',
  events: {
    'change input': 'updateModel'
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config
    this.identifier = this.config.identifier || this.defaultIdentifier

    BaseForm.prototype.initialize.apply(this, arguments)
  },

  /**
   * Update the model with the family value
   * @param  {Object} event jQuery event
   */
  updateModel (event) {
    this.getFormModel().set('family', event.target.value)
  },

  /**
   * Parses the family results and translates the labels
   * @param  {Array} families An array of family entities
   * @return {Array}          The formatted array of families
   */
  parseResults (families) {
    const locale = UserContext.get('catalogLocale')
    const data = {
      results: []
    }

    for (const family in families) {
      data.results.push({
        id: family,
        text: i18n.getLabel(families[family].labels, locale, family)
      })
    }
    ;

    return data
  },

  /**
   * Use the family fetcher to get the families
   * @param  {HTMLElement}   element  The select2 element
   * @param  {Function} callback
   */
  fetchFamilies (element, callback) {
    const locale = UserContext.get('catalogLocale')
    const formData = this.getFormData().family

    if (formData) {
      FetcherRegistry.getFetcher('family')
        .fetch(formData)
        .then(function (family) {
          const {labels, code} = family
          const details = {
            id: code,
            text: i18n.getLabel(labels, locale, code)
          }

          callback(details)
        })
    }
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render () {
    if (!this.configured) return this

    const errors = this.getRoot().validationErrors || []

    this.$el.html(this.template({
      label: __('pim_enrich.form.product.change_family.modal.empty_selection'),
      code: this.getFormData().family,
      errors: errors.filter(error => error.path === this.identifier)
    }))

    this.delegateEvents()

    var options = {
      allowClear: true,
      initSelection: this.fetchFamilies.bind(this),
      ajax: {
        url: Routing.generate('pim_enrich_family_rest_index'),
        results: this.parseResults.bind(this),
        quietMillis: 250,
        cache: true,
        data (term) {
          return {
            search: term,
            options: {
              locale: UserContext.get('catalogLocale')
            }
          }
        }
      }
    }

    initSelect2.init(this.$('input'), options).select2('val', [])
  }
})
