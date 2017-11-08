/**
 * A select2 field displaying family variants dependent on the family field in the same parent form.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form/common/fields/field',
    'pim/form',
    'pim/fetcher-registry',
    'pim/router',
    'pim/user-context',
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    __,
    BaseField,
    BaseForm,
    FetcherRegistry,
    Routing,
    UserContext,
    template
) {
    return BaseField.extend({
        events: {
            'change select': function (event) {
                this.setData({
                    family_variant: event.target.value
                });
            }
        },

        template: _.template(template),
        defaultLabel: __('pim_enrich.form.product_model.choose_variant'),
        fieldLabel: 'family_variant',
        fieldId: 'family_variant',
        fieldName: 'family_variant',
        readOnly: true,
        choices: [],
        defaultValue: null,
        errors: [],

        /**
         * {@inheritdoc}
         */
        initialize(config) {
            this.config = config.config;
            this.choices = [];
            this.errors = [];
            this.defaultValue = null;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        configure() {
            this.listenTo(
                this.getRoot(),
                'pim_enrich:form:entity:post_update',
                this.updateOnFamilyChange.bind(this)
            );

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Listen for changes on the 'family' key in the form data
         */
        updateOnFamilyChange(changed) {
            const family = this.getFormData().family;

            if (_.isEmpty(family)) {
                return this.resetSelectField();
            }

            if (changed.family) {
                this.getFamilyIdFromCode(family)
                    .then(this.renderVariantsForFamily.bind(this));
            }
        },

        /**
         * Clear the choices and selected value of the field and re-render
         */
        resetSelectField() {
            this.choices = [];
            this.readOnly = true;
            this.errors = [];
            this.$('select.select2').select2('val', '');
            this.setData(
                { family_variant: null },
                { unset: true, silent: true }
            );
            this.render();
        },

        /**
         * {@inheritdoc}
         */
        render() {
            const errors = this.getRoot().validationErrors || [];
            this.errors = errors.filter(error => error.path === this.fieldName)

            return BaseField.prototype.render.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        renderInput() {
            return this.template({
                fieldId: this.fieldId,
                fieldName: this.fieldName,
                value: this.defaultValue || this.getFormData()[this.fieldName],
                choices: this.choices,
                multiple: false,
                readOnly: this.readOnly,
                labels: {
                    defaultLabel: this.defaultLabel
                }
            });
        },

        /**
         * Get the code for a given family id
         * @return {Promise}
         */
        getFamilyIdFromCode(code) {
            return FetcherRegistry.getFetcher('family')
                .fetch(code)
                .then(family => family.meta.id);
        },

        /**
         * Loads the variants for a family based on a family id then render
         * a select2 dropdown with the variants
         * @param  {String} family The id of a family
         * @return {Promise}       The JSON load promise
         */
        renderVariantsForFamily(family) {
            const locale = UserContext.get('catalogLocale');
            const variantLoadUrl = Routing.generate(this.config.loadUrl, {
                alias: 'family-variant-grid',
                'family-variant-grid[family_id]': family,
                'family-variant-grid[localeCode]': locale
            });

            this.resetSelectField();

            return $.getJSON(variantLoadUrl).then((response) => {
                const responseJSON = JSON.parse(response.data);
                const variantData = responseJSON.data;

                this.readOnly = false;
                this.choices = this.formatChoices(variantData);

                if (variantData.length === 1) {
                    this.defaultValue = variantData[0].familyVariantCode;
                    this.setData(
                        { family_variant: this.defaultValue },
                        { silent: true }
                    );
                }

                this.render();
            });
        },

        /**
         * Format the variant data to return an object
         * Example:
         *     {
         *         'Clothing color and size': 'clothing_color_and_size',
         *         ...
         *     }
         * @param  {Array} variants An array of variant for a family
         * @return {Object}
         */
        formatChoices(variants) {
            const choices = {};

            for (const variant in variants) {
                const code = variants[variant].familyVariantCode;
                choices[code] = variants[variant].label;
            }

            return choices;
        },

        /**
         * {@inheritdoc}
         */
        postRender() {
            this.$('select.select2').select2();
        }
    });
});
