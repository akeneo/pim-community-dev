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
        'routing',
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
        Routing,
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
                this.config = _.defaults(meta.config, {fieldModules: {}, codeFieldModule: null});

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
                    FetcherRegistry.getFetcher('product-model-by-code').fetch(parentCode),
                    FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                ).then((familyVariant, parent, identifier) => {
                    const currentLevel = parent.meta.level + 1;
                    const numberOfLevels = familyVariant.variant_attribute_sets.length;

                    this.getAxesAttributes(familyVariant, currentLevel)
                        .then((axesAttributes) => {
                            return $.when.apply($, axesAttributes.map(
                                (attribute) => this.createAttributeField(attribute)
                            ));
                        })
                        .then((...fields) => {
                            if (currentLevel === numberOfLevels) {
                                return this.createAttributeField(identifier).then(
                                    (identifierField) => fields.concat(identifierField)
                                );
                            }

                            return this.createProductModelCodeField().then(
                                (codeField) => fields.concat(codeField)
                            );
                        })
                        .then((fields) => {
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
             * This logic could be extracted to a factory later.
             *
             * @param {Object} attribute
             *
             * @returns {Promise}
             */
            createAttributeField(attribute) {
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
                        if ('pim_reference_data_simpleselect' === attribute.type) {
                            return FetcherRegistry.getFetcher('reference-data-configuration')
                                .fetchAll()
                                .then((config) => {
                                    field.setChoiceUrl(
                                        Routing.generate(
                                            'pim_ui_ajaxentity_list',
                                            {
                                                'class': config[attribute.reference_data_name].class,
                                                'dataLocale': UserContext.locale,
                                                'collectionId': attribute.id,
                                                'options': {'type': 'code'}
                                            }
                                        )
                                    );

                                    return field;
                                });
                        }

                        if ('pim_catalog_metric' === attribute.type) {
                            field.setMetricFamily(attribute.metric_family);
                        }

                        if ('pim_catalog_simpleselect' === attribute.type) {
                            field.setChoiceUrl(
                                Routing.generate('pim_enrich_attributeoption_get', {identifier: attribute.code})
                            );
                        }

                        return field;
                    })
                    .then((field) => {
                        return field.configure().then(() => field);
                    });
            },

            /**
             * Instantiates a field view corresponding to the product model code.
             *
             * @returns {Promise}
             */
            createProductModelCodeField() {
                return FormBuilder
                    .getFormMeta(this.config.codeFieldModule)
                    .then((formMeta) => {
                        const newFormMeta = Object.assign({}, formMeta);
                        newFormMeta.config.fieldName = 'code';

                        return FormBuilder.buildForm(newFormMeta);
                    })
                    .then((field) => {
                        return field.configure().then(() => field);
                    });
            }
        });
    }
);
