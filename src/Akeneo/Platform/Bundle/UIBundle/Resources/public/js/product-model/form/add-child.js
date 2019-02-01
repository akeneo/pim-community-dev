'use strict';

/**
 * Modal to create a product model child.
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
        'pim/template/product-model-edit-form/add-child-form'
    ], (
        $,
        _,
        __,
        BaseForm,
        Routing,
        template
    ) => {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render() {
                const illustrationClass = this.getIllustrationClass();
                this.$el.html(this.template({
                    illustrationClass,
                    okText: __('pim_common.save'),
                }));
                this.renderExtensions();
            },

            /**
             * Get the correct illustration class for products or product models
             *
             * @return {String}
             */
            getIllustrationClass() {
                const formData = this.getFormData();
                const hasFamilyVariant = formData.hasOwnProperty('family_variant');

                return hasFamilyVariant ? 'product-model' : 'products';
            },

            /**
             * Save the product model child in the backend.
             *
             * @param {String} route
             *
             * @return {Promise}
             */
            saveProductModelChild(route) {
                this.trigger('pim_enrich:form:entity:pre_save');

                return $.post(
                    Routing.generate(route),
                    JSON.stringify(this.getFormData())
                ).fail((xhr) => {
                    this.trigger('pim_enrich:form:entity:validation_error', xhr.responseJSON.values);
                });
            }
        });
    }
);
