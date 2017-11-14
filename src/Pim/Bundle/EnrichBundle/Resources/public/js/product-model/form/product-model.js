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
        'pim/form/common/fields/simple-select-async',
        'pim/router',
        'pim/user-context',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        __,
        SimpleSelectAsync,
        Routing,
        UserContext,
        FetcherRegistry
    ) {
        return SimpleSelectAsync.extend({
            previousFamilyVariant: null,
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_update',
                    this.updateOnFamilyVariantChange.bind(this)
                );

                this.listenTo(
                    this,
                    'mass-edit:update-read-only',
                    this.setReadOnly.bind(this)
                );

                return SimpleSelectAsync.prototype.configure.apply(this, arguments);
            },

            /**
             * Updates the choice URL when the model change
             */
            updateOnFamilyVariantChange() {
                if (this.getFormData().family_variant !== this.previousFamilyVariant) {
                    this.previousFamilyVariant = this.getFormData().family_variant;
                    this.setData({[this.fieldName]: null});

                    this.render();
                }
            },

            /**
             * {@inheritdoc}
             */
            select2Data() {
                let result = SimpleSelectAsync.prototype.select2Data.apply(this, arguments);
                result.options.family_variant = this.getFormData().family_variant;

                return result;
            },

            /**
             * {@inheritdoc}
             */
            convertBackendItem(item) {
                return {
                    id: item.code,
                    text: item.meta.label[UserContext.get('uiLocale')]
                };
            },

            /**
             * {@inheritdoc}
             */
            isReadOnly() {
                return this.readOnly || !this.getFormData().family_variant;
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
            },

            /**
             * Updates the readOnly parameter to avoid edition of the field
             *
             * @param {Boolean} readOnly
             */
            setReadOnly(readOnly) {
                this.readOnly = readOnly;
            }
        });
    }
);
