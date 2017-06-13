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
        'pim/template/form/creation/type'
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
                const resultLength = Object.keys(types).length;
                const locale = UserContext.get('catalogLocale');

                const data = {
                    more: 20 === resultLength,
                    results: []
                };

                types.forEach(value => {
                    const { code, labels } = value
                    data.results.push({
                        id: code,
                        text: i18n.getLabel(labels, locale, code)
                    });
                })

                return data;
            },


            /**
             * Uses the fetcher registry to get a list of group types to use in the select2
             *
             * @param  {Object} formData An object containing group code and type
             * @param  {jQueryElement} element  The select2 element
             * @param  {Function} callback
             */
            fetchGroupTypes(formData, element, callback) {
                if (!formData.type) return;

                FetcherRegistry.getFetcher('grouptype')
                .fetch(formData.type)
                .then(type => {
                    callback({
                        id: type.code,
                        text: i18n.getLabel(
                            type.labels,
                            UserContext.get('catalogLocale'),
                            type.code
                        )
                    });
                });
            },

            /**
             * Renders the form
             *
             * @return {Promise}
             */
            render() {
                if (!this.configured) return this;

                const formData = this.getFormData();
                const locale = UserContext.get('catalogLocale')

                this.$el.html(this.template({
                    label: __('pim_enrich.form.group.tab.properties.type'),
                    type: formData.type,
                    required: __('pim_enrich.form.required')
                }));

                this.delegateEvents();

                var options = {
                    initSelection: this.fetchGroupTypes.bind(this, formData),
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
