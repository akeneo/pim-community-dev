/**
 * This module displays a product model select2
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/mass-edit-form/product/mass-edit-field',
        'pim/router',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/media-url-generator',
        'pim/template/product/form/variant-navigation/product-model-item'
    ],
    function (
        $,
        _,
        __,
        MassEditField,
        Routing,
        UserContext,
        FetcherRegistry,
        MediaUrlGenerator,
        templateProductModel
    ) {
        return MassEditField.extend({
            previousFamilyVariant: null,
            templateProductModel: _.template(templateProductModel),

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_update',
                    this.onPostUpdate.bind(this)
                );

                return MassEditField.prototype.configure.apply(this, arguments);
            },

            /**
             * When the model data is updated with a new family variant, drops the previous value and re-render the
             * field.
             */
            onPostUpdate() {
                if (this.getFormData().family_variant !== this.previousFamilyVariant) {
                    this.previousFamilyVariant = this.getFormData().family_variant;
                    this.setData({[this.fieldName]: null});

                    this.render();
                }
            },

            /**
             * {@inheritdoc}
             *
             * This method overrides the previous one to be able to format the result and add an image and set a
             * custom template.
             */
            getSelect2Options() {
                let options = MassEditField.prototype.getSelect2Options.apply(this, arguments);

                options.dropdownCssClass = 'variant-navigation';
                options.formatResult = (item, $container) => {
                    const filePath = (null !== item.image) ? item.image.filePath : null;
                    const entity = {
                        label: item.text,
                        image: MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small')
                    };

                    $container.append(
                        this.templateProductModel({entity: entity, getClass: this.getCompletenessBadgeClass})
                    );
                };

                return options;
            },

            /**
             * {@inheritdoc}
             */
            select2Data() {
                let result = MassEditField.prototype.select2Data.apply(this, arguments);
                result.options.family_variant = this.getFormData().family_variant;

                return result;
            },

            /**
             * {@inheritdoc}
             */
            convertBackendItem(item) {
                return {
                    id: item.code,
                    text: `${item.code} - ${item.meta.label[UserContext.get('catalogLocale')]}`,
                    image: item.meta.image || null
                };
            },

            /**
             * {@inheritdoc}
             */
            isReadOnly() {
                return !this.getFormData().family_variant || MassEditField.prototype.isReadOnly.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            select2InitSelection(element, callback) {
                const id = $(element).val();
                if ('' !== id) {
                    FetcherRegistry
                        .getFetcher('product-model-by-code')
                        .fetch(id)
                        .then((productModel) => {
                            callback(this.convertBackendItem(productModel));
                        });
                }
            }
        });
    }
);
