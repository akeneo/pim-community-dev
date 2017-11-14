'use strict';

/**
 * Family select2 to be added in a creation form
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'backbone',
    'routing',
    'pim/form',
    'pim/user-context',
    'pim/i18n',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/initselect2',
    'pim/template/form/creation/family',
    'jquery.select2'
], function(
    $,
    _,
    Backbone,
    Routing,
    BaseForm,
    UserContext,
    i18n,
    __,
    FetcherRegistry,
    initSelect2,
    template
) {

    return BaseForm.extend({
        template: _.template(template),
        validationErrors: {},
        config: {
            fieldLabel: '',
            required: false
        },
        events: {
            'change input': 'updateModel'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = Object.assign(this.config, config.config || {});

            if (!this.config.identifier || !this.config.loadUrl) {
                throw new Error('You must define the identifier and loadUrl for this field');
            }

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * Update the model with the family value
         * @param  {Object} event jQuery event
         */
        updateModel(event) {
            this.setData({ family: event.target.value });
        },

        /**
         * Parses the family results and translates the labels
         * @param  {Array} families An array of family entities
         * @return {Array}          The formatted array of families
         */
        parseResults(families) {
            const locale = UserContext.get('catalogLocale');
            const data = { results: [] };

            for (const family in families) {
                data.results.push({
                    id: family,
                    text: i18n.getLabel(families[family].labels, locale, family)
                });
            };

            return data;
        },

        /**
         * Use the family fetcher to get the families
         * @param  {HTMLElement}   element  The select2 element
         * @param  {Function} callback
         */
        fetchFamilies(element, callback) {
            const locale = UserContext.get('catalogLocale');
            const formData = this.getFormData().family;

            if (formData) {
                FetcherRegistry.getFetcher('family')
                .fetch(formData)
                .then(function(family) {
                    const { labels, code } = family;
                    callback({
                        id: code,
                        text: i18n.getLabel(labels, locale, code)
                    });
                });
            }
        },

        /**
         * Renders the form
         *
         * @return {Promise}
         */
        render() {
            if (!this.configured) return this;

            const errors = this.getRoot().validationErrors || [];

            this.$el.html(this.template({
                label: __('pim_enrich.form.product.change_family.modal.empty_selection'),
                code: this.getFormData().family,
                errors: errors.filter(error => error.path === this.identifier),
                fieldLabel: __(this.config.fieldLabel),
                required: this.config.required,
                requiredLabel: __('pim_enrich.form.required')
            }));

            this.delegateEvents();

            var options = {
                allowClear: true,
                initSelection: this.fetchFamilies.bind(this),
                ajax: {
                    url: Routing.generate(this.config.loadUrl),
                    results: this.parseResults.bind(this),
                    quietMillis: 250,
                    cache: true,
                    data(term) {
                        return {
                            search: term,
                            options: {
                                locale: UserContext.get('catalogLocale')
                            }
                        };
                    }
                }
            };

            initSelect2.init(this.$('input'), options).select2('val', []);
        }
    });
});
