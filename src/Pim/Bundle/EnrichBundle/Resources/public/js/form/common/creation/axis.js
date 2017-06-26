/**
 * Group type select2 to be added in a creation form
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define(
    [
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
        'pim/template/form/creation/axis'
    ],
    function (
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

        console.log('axis');

        return BaseForm.extend({
            template: _.template(template),
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
                this.getFormModel().set('type', event.target.value);
            },


            /**
             * Parses each group type for the select display
             *
             * @param  {Array} types The search results
             * @return {Object}
             */
            parseResults(types) {
                console.log('parseResults', this);
                const resultLength = Object.keys(types).length;
                const locale = UserContext.get('catalogLocale');

                const data = {
                    more: 20 === resultLength,
                    results: []
                };

                _.reject(types, { is_variant: true }).forEach(value => {
                    const { code, labels } = value;

                    data.results.push({
                        id: code,
                        text: i18n.getLabel(labels, locale, code)
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
                console.log('render', this);

                if (!this.configured) return this;

                const formData = this.getFormData();
                const locale = UserContext.get('catalogLocale');

                this.$el.html(this.template({
                    label: __('pim_enrich.form.group.tab.properties.type'),
                    type: formData.type,
                    required: __('pim_enrich.form.required')
                }));

                this.delegateEvents();

                var options = {
                    allowClear: true,
                    ajax: {
                        url: Routing.generate('pim_enrich_grouptype_rest_index'),
                        results: this.parseResults,
                        quietMillis: 250,
                        cache: true,
                        data(search, page) {
                            return {
                                search,
                                options: { limit: 20, page, locale }
                            };
                        }
                    }
                };

                initSelect2.init(this.$('[data-code="type"] input'), options);
            }
        });
    }
);
