

/**
 * Attribute group edit controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore';
import __ from 'oro/translator';
import BaseController from 'pim/controller/base';
import FormBuilder from 'pim/form-builder';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import Dialog from 'pim/dialog';
import PageTitle from 'pim/page-title';
import Error from 'pim/error';
export default BaseController.extend({
            /**
             * {@inheritdoc}
             */
    renderRoute: function (route) {
        return FetcherRegistry.getFetcher('attribute-group').fetch(route.params.identifier, {cached: false})
                    .then(function (attributeGroup) {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({
                            'group.label':
                            _.escape(attributeGroup.labels[UserContext.get('catalogLocale')])
                        });

                        return FormBuilder.build('pim-attribute-group-edit-form')
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(attributeGroup);

                                form.trigger('pim_enrich:form:entity:post_fetch', attributeGroup);

                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                    .fail(function (response) {
                        var message = response.responseJSON ? response.responseJSON.message : __('error.common');

                        var errorView = new Error(message, response.status);
                        errorView.setElement(this.$el).render();
                    });
    }
});

