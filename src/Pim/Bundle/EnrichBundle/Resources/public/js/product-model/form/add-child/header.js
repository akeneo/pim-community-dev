'use strict';

/**
 * Form header of the product model child creation modal.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'jquery',
        'underscore',
        'oro/translator',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/template/product-model-edit-form/add-child-form-header'
    ],
    (
        BaseForm,
        $,
        _,
        __,
        i18n,
        UserContext,
        FetcherRegistry,
        template
    ) => {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render() {
                const parentCode = this.getFormData().parent;

                FetcherRegistry
                    .getFetcher('product-model-by-code')
                    .fetch(parentCode)
                    .then((parent) => {
                        FetcherRegistry
                            .getFetcher('family-variant')
                            .fetch(parent.family_variant)
                            .then((familyVariant) => {
                                this.getAxesAttributes(familyVariant, parent.meta.level + 1)
                                    .then((axesAttributes) => {
                                        const catalogLocale = UserContext.get('catalogLocale');
                                        const axesLabels = axesAttributes.map((attribute) => {
                                            return i18n.getLabel(attribute.labels, catalogLocale, attribute.code);
                                        });

                                        this.$el.html(
                                            this.template({
                                                __: __,
                                                axes: axesLabels.sort().join(', ')
                                            })
                                        );
                                    });
                            });
                    });
            },

            /**
             * Looks for the attributes set corresponding to the specified level of the family variant
             * and fetches its axes attributes.
             *
             * @param {Object} familyVariant
             * @param {Number} level
             *
             * @returns {Promise}
             */
            getAxesAttributes(familyVariant, level) {
                const variantAttributeSets = familyVariant.variant_attribute_sets;
                const variantAttributeSetForLevel = variantAttributeSets.find(
                    (variantAttributeSet) => variantAttributeSet.level === level
                );

                return FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(variantAttributeSetForLevel.axes);
            }
        });
    }
);
