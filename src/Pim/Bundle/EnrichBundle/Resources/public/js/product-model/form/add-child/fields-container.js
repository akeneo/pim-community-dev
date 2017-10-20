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
        FormBuilder,
        template
    ) => {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize(config) {
                this.config = _.defaults(config, {fieldModules: {}});

                BaseForm.prototype.initialize.apply(arguments);
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
                    this.getAxesAttributes(familyVariant, parent.meta.level + 1)
                        .then((axesAttributes) => {
                            return $.when(axesAttributes.map((attribute) => this.createField(attribute)))
                        })
                        .then((...fields) => {
                            let position = 100;
                            fields.forEach((field) => {
                                this.addExtension(
                                    field.code,
                                    field,
                                    'self',
                                    position++
                                );
                            });
                        });
                });
            },

            getAxesAttributes: function(familyVariant, level) {
                const variantAttributeSets = familyVariant.variant_attribute_sets;
                const variantAttributeSetForLevel = variantAttributeSets.find((variantAttributeSet) => {
                    return variantAttributeSet.level === level;
                });

                FetcherRegistry
                    .getFetcher('attribute')
                    .fetchByIdentifiers(variantAttributeSetForLevel.axes)
            },

            createField(attribute) {
                const fieldModuleName = this.config.fieldModules[attribute.field_type];

                if (undefined === fieldModuleName) {
                    throw new Error('No module set for field type "' + attribute.field_type + '"');
                }

                return FormBuilder.buildForm(fieldModuleName)
                    .then((field) => {
                        if ('pim_catalog_metric' === attribute.type) {
                            field.setMetricFamily(attribute.metric_family);
                        }

                        return field;
                    });
            }
        });
    }
);
