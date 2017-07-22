/**
 * Group type select2 to be added in a creation form
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import Routing from 'routing'
import BaseForm from 'pim/form'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
import __ from 'oro/translator'
import template from 'pim/template/form/creation/job'
import BaseFetcher from 'pim/base-fetcher'

export default BaseForm.extend({
  options: {},
  template: _.template(template),
  events: {
    'change select': 'updateModel'
  },

  /**
   * Configure the form
   *
   * @return {Promise}
   */
  configure () {
    const jobType = this.options.config.type
    const fetcher = new BaseFetcher({
      urls: {
        list: this.options.config.url
      }
    })

    return fetcher.search({
      jobType
    }).then((jobs) => {
      this.jobs = jobs
      BaseForm.prototype.configure.apply(this, arguments)
    })
  },

  /**
   * Model update callback
   */
  updateModel (event) {
    const option = this.$(event.target)
    const optionParent = $(':selected', option).closest('optgroup')

    this.getFormModel().set({
      'alias': option.val(),
      'connector': optionParent.attr('label')
    })
  },

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render () {
    if (!this.configured) return this

    const errors = this.getRoot().validationErrors || []
    const identifier = this.options.config.identifier || 'alias'

    this.$el.html(this.template({
      label: __(this.options.config.label),
      jobs: this.jobs,
      required: __('pim_enrich.form.required'),
      selectedJobType: this.getFormData().alias,
      errors: errors.filter(error => error.path === identifier),
      __
    }))

    this.delegateEvents()
  }
})
