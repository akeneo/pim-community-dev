import _ from 'underscore'
import __ from 'oro/translator'
import BaseController from 'pim/controller/base'
import FormBuilder from 'pim/form-builder'
import FetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import PageTitle from 'pim/page-title'
import Error from 'pim/error'
import i18n from 'pim/i18n'

export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route) {
    return FetcherRegistry.getFetcher('family').fetch(
      route.params.code,
      {
        cached: false,
        apply_filters: false
      }
    ).then(function (family) {
      if (!this.active) {
        return
      }

      var label = _.escape(
        i18n.getLabel(
          family.labels,
          UserContext.get('catalogLocale'),
          family.code
        )
      )

      PageTitle.set({
        'family.label': _.escape(label)
      })

      FormBuilder.build(family.meta.form)
        .then(function (form) {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event)
          })
          form.setData(family)
          form.trigger('pim_enrich:form:entity:post_fetch', family)
          form.setElement(this.$el).render()
        }.bind(this))
    }.bind(this))
      .fail(function (response) {
        var message = response.responseJSON ? response.responseJSON.message : __('error.common')

        var errorView = new Error(message, response.status)
        errorView.setElement(this.$el).render()
      })
  }
})
