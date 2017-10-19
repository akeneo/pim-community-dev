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
                const family_variant = event.target.value;
                this.setData({ family_variant });
                this.getFormModel().unset('family');
            }
        },

        template: _.template(template),
        defaultLabel: 'Choose a variant',
        fieldLabel: 'family_variant',
        fieldId: 'family_variant',
        fieldName: 'family_variant',
        readOnly: true,
        choices: [],

        /**
         * {@inheritdoc}
         */
        initialize(config) {
            this.config = config.config;
            this.choices = [];

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
         * This field is dependent on the 'family' field in the same form. It will listen
         * to changes on the form model to see if we need to render the family variants
         * for the selected family.
         */
        updateOnFamilyChange() {
            const formModel = this.getFormModel();

            if (formModel.hasChanged('family')) {
                this.renderVariantsForFamily(formModel.get('family'));
            }
        },

        /**
         * {@inheritdoc}
         */
        renderInput() {
            return this.template({
                fieldId: this.fieldId,
                fieldName: this.fieldName,
                value: this.getFormData()[this.fieldName],
                choices: this.choices,
                multiple: false,
                readOnly: this.readOnly,
                labels: {
                    defaultLabel: this.defaultLabel
                }
            });
        },

        /**
         * Loads the variants for a family based on a family id and renders
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

            return $.getJSON(variantLoadUrl).then((response) => {
                const variants = JSON.parse(response.data);
                this.readOnly = false;
                this.choices = this.formatChoices(variants.data);
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
         * @param  {[type]} variants [description]
         * @return {[type]}          [description]
         */
        formatChoices(variants) {
            const choices = {};

            _.each(variants, variant => {
                choices[variant.familyVariantCode] = variant.label
            })

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
