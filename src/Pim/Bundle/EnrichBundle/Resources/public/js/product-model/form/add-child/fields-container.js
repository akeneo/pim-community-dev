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
            initialize(meta) {
                this.config = _.defaults(meta.config, {fieldModules: {}});

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const familyVariantCode = this.getFormData().family_variant;
                const parentCode = this.getFormData().parent;

                this.$el.html(this.template());

                $.when(
                    FetcherRegistry.getFetcher('family-variant').fetch(familyVariantCode),
                    FetcherRegistry.getFetcher('product-model-by-code').fetch(parentCode)
                ).then((familyVariant, parent) => {
                    this.getAxesAttributes(familyVariant, parent.meta.level + 1)
                        .then((axesAttributes) => {
                            return $.when.apply($, axesAttributes.map((attribute) => this.createField(attribute)));
                        })
                        .then((...fields) => {
                            let position = 100;
                            fields.forEach((field) => {
                                this.addExtension(
                                    field.code,
                                    field,
                                    'content',
                                    position++
                                );
                            });

                            this.renderExtensions();
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

                return FetcherRegistry
                    .getFetcher('attribute')
                    .fetchByIdentifiers(variantAttributeSetForLevel.axes);
            },

            /**
             * Instantiate a field view corresponding to an attribute.
             * The "field_type" of the attribute must be mapped to a view key in the config of this module.
             * The meta under this key is then modified on-the-fly before instantiation using attribute
             * code to make the field unique.
             *
             * @param {Object} attribute
             *
             * @returns {Promise}
             */
            createField(attribute) {
                const fieldModuleName = this.config.fieldModules[attribute.field_type];

                if (undefined === fieldModuleName) {
                    throw new Error('No module set for field type "' + attribute.field_type + '"');
                }

                return FormBuilder
                    .getFormMeta(fieldModuleName)
                    .then((formMeta) => {
                        const newFormMeta = Object.assign({}, formMeta);
                        newFormMeta.code += '-' + attribute.code;
                        newFormMeta.config.fieldName = attribute.code;

                        return FormBuilder.buildForm(newFormMeta);
                    })
                    .then((field) => {
                        if ('pim_catalog_metric' === attribute.type) {
                            field.setMetricFamily(attribute.metric_family);
                        }

                        return field.configure().then(() => {
                            return field;
                        });
                    });
            }
        });
    }
);
