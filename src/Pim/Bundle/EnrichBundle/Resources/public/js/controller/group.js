

import _ from 'underscore'
import __ from 'oro/translator'
import BaseController from 'pim/controller/base'
import FormBuilder from 'pim/form-builder'
import FetcherRegistry from 'pim/fetcher-registry'
import UserContext from 'pim/user-context'
import Dialog from 'pim/dialog'
import PageTitle from 'pim/page-title'
import Error from 'pim/error'
import i18n from 'pim/i18n'
export default BaseController.extend({
    initialize: function () {
        this.config = __moduleConfig
    },

            /**
             * {@inheritdoc}
             */
    renderRoute: function (route) {
        return FetcherRegistry.getFetcher(this.config.fetcher).fetch(route.params.code, {cached: false})
                    .then(function (group) {
                        if (!this.active) {
                            return
                        }

                        var label = _.escape(
                            i18n.getLabel(
                                group.labels,
                                UserContext.get('catalogLocale'),
                                group.code
                            )
                        )

                        PageTitle.set({'group.label': label })

                        FormBuilder.build(group.meta.form)
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event)
                                })
                                form.setData(group)
                                form.trigger('pim_enrich:form:entity:post_fetch', group)
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

