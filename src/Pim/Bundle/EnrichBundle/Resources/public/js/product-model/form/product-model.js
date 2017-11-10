/**
 * TODO
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
        'pim/form/common/fields/field',
        'pim/router',
        'pim/user-context'
    ],
    function (
        $,
        _,
        __,
        BaseField,
        Routing,
        UserContext
    ) {
        return BaseField.extend({
            familyVariant: null,
            events: {
                'change input': function (event) {
                    this.setData({
                        product_model: event.target.value,
                    }, {
                        silent: true
                    });
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseField.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_update',
                    this.updateOnFamilyVariantChange.bind(this)
                );

                return BaseField.prototype.configure.apply(this, arguments);
            },

            /**
             * Method called on family variant update
             */
            updateOnFamilyVariantChange() {
                const previousFamilyVariant = this.familyVariant;
                this.familyVariant = this.getFormData().family_variant || null;

                if (previousFamilyVariant !== this.familyVariant) {
                    this.$('.field-input').replaceWith(this.renderInput({}));
                    this.postRender();
                }
            },

            /**
             * Returns the results to be usable in select2
             * @param results
             * @returns {Object}
             */
            parseResults(results) {
                const data = {
                    more: false,
                    results: []
                };
                _.each(results, function (value, key) {
                    data.results.push({
                        id: key,
                        text: value.meta.label[UserContext.get('uiLocale')]
                    });
                });

                return data;
            },

            /**
             * {@inheritdoc}
             */
            renderInput(templateContext) {
                const template = _.template('<input>');

                return template({
                    readOnly: templateContext.readOnly
                });
            },

            /**
             * {@inheritdoc}
             */
            postRender() {
                let options = {
                    allowClear: true,
                    placeholder: __('pim_enrich.form.product_model.choose_product_model'),
                    ajax: {
                        url: Routing.generate(this.config.loadUrl),
                        results: this.parseResults.bind(this),
                        quietMillis: 250,
                        cache: true,
                        data: (term) => {
                            return {
                                search: term,
                                options: {
                                    family_variant: this.familyVariant
                                }
                            };
                        }
                    }
                };

                this.$('input').attr('disabled', this.isReadOnly() ? 'disabled' : null);

                if (this.getFormData().product_model) {
                    options.choices = [{id: this.getFormData().product_model, text: 'toto'}];
                }

                this.$('input').select2(options);

                if (this.getFormData().product_model) {
                    // this.$('input').select2('val', this.getFormData().product_model);
                }
            },

            /**
             * Should the field be in readonly mode?
             *
             * @returns {Boolean}
             */
            isReadOnly() {
                return this.familyVariant === null;
            }
        });
    }
);
