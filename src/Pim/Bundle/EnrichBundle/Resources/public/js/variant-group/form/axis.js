/**
 * Group type select2 to be added in a creation form
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
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
import template from 'pim/template/variant-group/form/axis'

export default BaseForm.extend({
  template: _.template(template),
  events: {
    'change input': 'updateModel'
  },

  /**
   * Update the model with the array of axes
   * @param  {Object} event jQuery event
   */
  updateModel (event) {
    const axes = event.target.value.split(',')
    this.getFormModel().set('axes', axes)
  },

  /**
   * Format the axes by changing format and translating labels
   * @param  {Array} axes             An array of fetched axes
   * @param  {Boolean} useTranslation Flag to use translations
   * @return {Array}                  An array of formatted axes
   */
  formatAxes (axes, useTranslation) {
    const locale = UserContext.get('catalogLocale')
    const formatted = []

    axes.forEach(axis => {
      const id = axis.code
      let text = axis.label

      if (useTranslation) {
        text = i18n.getLabel(axis.labels, locale, id)
      }

      formatted.push({
        id,
        text
      })
    })

    return formatted
  },

  /**
   * Parses each group type for the select display
   *
   * @param  {Array} types The search results
   * @return {Object}
   */
  parseResults (axes) {
    return {
      results: this.formatAxes(axes)
    }
  },

  /**
   * Get axes using the fetcher. This method is called by the select2 instance
   * @param  {HTMLElement}   element  The select2 element
   * @param  {Function} callback
   * @return {Promise}                Return the request as a promise
   */
  fetchAxes (element, callback) {
    const axes = this.getFormData().axes
    if (!axes) return

    return FetcherRegistry.getFetcher('attribute')
      .fetchByIdentifiers(axes)
      .then(fetchedAxes => callback(this.formatAxes(fetchedAxes, true)))
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render () {
    if (!this.configured) return this

    const locale = UserContext.get('catalogLocale')
    const errors = this.getRoot().validationErrors || []

    this.$el.html(this.template({
      label: 'Axis',
      required: __('pim_enrich.form.required'),
      help: __('pim_enrich.form.variant_group.axis.help'),
      errors: errors.filter(error => error.message.includes('axis'))
    }))

    this.delegateEvents()

    var options = {
      allowClear: true,
      multiple: true,
      initSelection: this.fetchAxes.bind(this),
      ajax: {
        url: Routing.generate('pim_enrich_attribute_axes_index'),
        results: this.parseResults.bind(this),
        quietMillis: 250,
        cache: true,
        data () {
          return {
            locale
          }
        }
      }
    }

    initSelect2.init(this.$('input'), options).select2('val', [])
    this.$('[data-toggle="tooltip"]').tooltip()
  }
})
