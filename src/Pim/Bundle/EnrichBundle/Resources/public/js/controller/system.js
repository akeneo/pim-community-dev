
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseController from 'pim/controller/base'
import FormBuilder from 'pim/form-builder'
import PageTitle from 'pim/page-title'
import Error from 'pim/error'
import Routing from 'routing'
export default BaseController.extend({
            /**
             * {@inheritdoc}
             */
  renderRoute: function () {
    return $.when(
                    FormBuilder.build('oro-system-config-form'),
                    $.get(Routing.generate('oro_config_configuration_system_get'))
                ).then(function (form, response) {
                  this.on('pim:controller:can-leave', function (event) {
                    form.trigger('pim_enrich:form:can-leave', event)
                  })
                  form.setData(response[0])
                  form.setElement(this.$el).render()
                }.bind(this)).fail(function (response) {
                  var message = response.responseJSON ? response.responseJSON.message : __('error.common')

                  var errorView = new Error(message, response.status)
                  errorView.setElement(this.$el).render()
                })
  }
})
