'use strict';

/**
 * Attribute group edit controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry.getFetcher('attribute-group').fetch(route.params.identifier, {cached: false})
                    .then((attributeGroup) => {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({
                            'group.label':
                            _.escape(attributeGroup.labels[UserContext.get('catalogLocale')])
                        });

                        return FormBuilder.build('pim-attribute-group-edit-form')
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(attributeGroup);

                                form.trigger('pim_enrich:form:entity:post_fetch', attributeGroup);

                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
