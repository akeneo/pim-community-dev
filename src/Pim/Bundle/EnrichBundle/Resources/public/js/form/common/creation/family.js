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
        events: {
            'change input': 'updateModel'
        },

        /**
             * Configure the form
             *
             * @return {Promise}
             */
        configure() {
            return $.when(
                FetcherRegistry.initialize(),
                BaseForm.prototype.configure.apply(this, arguments)
            );
        },

        /**
             * Model update callback
             */
        updateModel(event) {
            this.getFormModel().set('family', event.target.value);
        },

        parseResults(families) {
            const locale = UserContext.get('catalogLocale');

            var data = {
                more: 20 === Object.keys(families).length,
                results: []
            };

            for (const family in families) {
                data.results.push({
                    id: family,
                    text: i18n.getLabel(families[family].labels, locale, family)
                });
            };

            return data;
        },

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
            if (!this.configured)
                return this;

            this.$el.html(this.template({
                label: __('pim_enrich.form.product.change_family.modal.empty_selection'),
                code: this.getFormData().family
            }));

            this.delegateEvents();

            var options = {
                allowClear: true,
                initSelection: this.fetchFamilies.bind(this),
                ajax: {
                    url: Routing.generate('pim_enrich_family_rest_index'),
                    results: this.parseResults.bind(this),
                    quietMillis: 250,
                    cache: true,
                    data(term, page) {
                        return {
                            search: term,
                            options: {
                                limit: 20,
                                page: page,
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
