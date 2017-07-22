
import _ from 'underscore'
import __ from 'oro/translator'
import BaseController from 'pim/controller/base'
import FormBuilder from 'pim/form-builder'
import FetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import Dialog from 'pim/dialog'
import PageTitle from 'pim/page-title'
import Error from 'pim/error'
export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route) {
    return FetcherRegistry.getFetcher('product').fetch(route.params.id, {
      cached: false
    })
      .then(function (product) {
        if (!this.active) {
          return
        }

        PageTitle.set({
          'product.sku': product.meta.label[UserContext.get('catalogLocale')]
        })

        return FormBuilder.build(product.meta.form)
          .then(function (form) {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event)
            })
            form.setData(product)

            form.trigger('pim_enrich:form:entity:post_fetch', product)

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
