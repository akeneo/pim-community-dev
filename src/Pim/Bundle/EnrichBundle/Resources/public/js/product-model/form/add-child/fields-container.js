'use strict';

/**
 * Form container for the axis fields of the product model child creation modal.
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
        'pim/form-config-provider',
        'pim/form-builder',
        'pim/template/product-model-edit-form/add-child-form-fields-container'
    ],
    (
        BaseForm,
        $,
        _,
        __,
        i18n,
        UserContext,
        FetcherRegistry,
        configProvider,
        formBuilder,
        template
    ) => {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize(config) {
                this.config = config;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                const familyVariantCode = this.getFormData().family_variant;
                const parentCode = this.getFormData().parent;

                $.when(
                    FetcherRegistry.getFetcher('family-variant').fetch(familyVariantCode),
                    FetcherRegistry.getFetcher('product-model-by-code').fetch(parentCode)
                ).then((familyVariant, parent) => {
                    const targetLevel = parent.meta.level + 1;
                    const variantAttributeSets = familyVariant.variant_attribute_sets;
                    const variantAttributeSetForLevel = variantAttributeSets.find((variantAttributeSet) => {
                        return variantAttributeSet.level === targetLevel;
                    });

                    FetcherRegistry
                        .getFetcher('attribute')
                        .fetchByIdentifiers(variantAttributeSetForLevel.axes)
                        .then((axesAttributes) => {
                            const fieldModules = axesAttributes
                                .map((attribute) => this.config[attribute.field_type]);
                                // .map((fieldKey) => formBuilder.buildForm(fieldKey));

                            console.log(fieldModules);
                        })
                    ;
                });
            }
        });
    }
);
