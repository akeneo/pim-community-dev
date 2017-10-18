'use strict';

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'jquery',
        'pim/fetcher-registry',
        'pim/form-config-provider',
        'pim/form-builder'
    ],
    (
        BaseForm,
        $,
        FetcherRegistry,
        configProvider,
        formBuilder
    ) => {
        return BaseForm.extend({
            initialize(config) {
                this.config = config;
            },

            render: function () {
                const familyVariantCode = this.getFormData().family_variant;
                const parentCode = this.getFormData().parent;
                const parentId = 3;

                $.when(
                    FetcherRegistry.getFetcher('family-variant').fetch(familyVariantCode),
                    FetcherRegistry.getFetcher('product-model').fetch(parentId)
                ).then((familyVariant, parent) => {
                    const parentLevel = parent.meta.level;
                    const variantAttributeSets = familyVariant.variant_attribute_sets;
                    const variantAttributeSetForLevel = variantAttributeSets.find((variantAttributeSet) => {
                        return variantAttributeSet.level === parentLevel + 1;
                    });

                    FetcherRegistry
                        .getFetcher('attribute')
                        .fetchByIdentifiers(variantAttributeSetForLevel.axes)
                        .then((attributes) => {
                            // const fieldModules = attributes
                            //     .map((attribute) => this.config[attribute.field_type])
                            //     .map((moduleName) => formBuilder.buildForm(moduleName));

                            console.log(attributes);
                        })
                    ;
                });
            }
        });
    }
);
