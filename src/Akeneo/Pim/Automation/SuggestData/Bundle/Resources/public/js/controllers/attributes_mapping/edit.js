'use strict';

/**
 * Attribute mapping edit controller
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                return FetcherRegistry
                    .getFetcher('suggest_data_family_mapping')
                    .fetch(route.params.identifier, {cached: false})
                    .then((familyMapping) => {
                        if (!this.active) {
                            return;
                        }

                        return FormBuilder.build('pim-suggest-data-settings-attributes-mapping-edit')
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(familyMapping);
                                form.trigger('pim_enrich:form:entity:post_fetch', familyMapping);
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
