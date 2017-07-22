/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

import _ from 'underscore'
import BaseController from 'pim/controller/base'
import FormBuilder from 'pim/form-builder'
import fetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import PageTitle from 'pim/page-title'
import Error from 'pim/error'
import i18n from 'pim/i18n'
export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route) {
    if (!this.active) {
      return
    }

    fetcherRegistry.getFetcher('attribute-group').clear()
    fetcherRegistry.getFetcher('locale').clear()
    fetcherRegistry.getFetcher('measure').clear()

    return fetcherRegistry.getFetcher('attribute').fetch(route.params.code, {
      cached: false
    })
      .then(function (attribute) {
        var label = _.escape(
          i18n.getLabel(
            attribute.labels,
            UserContext.get('catalogLocale'),
            attribute.code
          )
        )

        PageTitle.set({
          'attribute.label': label
        })

        var formName = attribute.type === 'pim_catalog_identifier'
          ? 'pim-attribute-identifier-edit-form'
          : 'pim-attribute-edit-form'

        return FormBuilder.buildForm(formName)
          .then(function (form) {
            form.setType(attribute.type)

            return form.configure().then(function () {
              return form
            })
          })
          .then(function (form) {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event)
            })
            form.setData(attribute)
            form.trigger('pim_enrich:form:entity:post_fetch', attribute)
            form.setElement(this.$el).render()
          }.bind(this))
      }.bind(this))
      .fail(function (response) {
        var errorView = new Error(response.responseJSON.message, response.status)
        errorView.setElement(this.$el).render()
      })
  }
})
