'use strict';

/**
 * Extension to display the variant navigation to browse variant product structure (parents and children)
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'routing',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/template/product-model-edit-form/add-child-form'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        Routing,
        i18n,
        UserContext,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());
                this.renderExtensions();
            },

            /**
             * Save the product model child in the backend.
             */
            saveProductModelChild() {
                this.trigger('pim_enrich:form:entity:pre_save');

                return $.post(
                    Routing.generate('pim_enrich_product_model_rest_create'),
                    JSON.stringify(this.getFormData())
                ).fail((xhr) => {
                    this.trigger('pim_enrich:form:entity:validation_error', xhr.responseJSON);
                });
            }
        });
    }
);
