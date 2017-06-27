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

            families.forEach(family => {
                console.log(family)
            })
            _.each(families, function(value, key) {
                data.results.push({
                    id: key,
                    text: i18n.getLabel(value.labels, locale, value.code)
                });
            });

            return data;
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
                ajax: {
                    url: Routing.generate('pim_enrich_family_rest_index'),
                    quietMillis: 250,
                    cache: true,
                    data(search, page) {
                        return {
                            search,
                            options: {
                                limit: 20,
                                page: page,
                                locale: UserContext.get('catalogLocale')
                            }
                        };
                    },
                    results: this.parseResults.bind(this)
                }
            };

            initSelect2.init(this.$('[data-code="family"] input'), options);
        }
    });
});
