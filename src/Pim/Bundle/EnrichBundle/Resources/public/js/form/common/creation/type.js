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
        'pim/template/form/creation/type',
        'jquery.select2'
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
            configure: function () {
                return $.when(
                    FetcherRegistry.initialize(),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },

            /**
             * Model update callback
             */
            updateModel: function (event) {
                this.getFormModel().set('type', event.target.value);
            },

            /**
             * Renders the form
             *
             * @return {Promise}
             */
            render: function () {
                if (!this.configured) return this;

                const formData = this.getFormData();

                this.$el.html(this.template({
                    label: __('pim_enrich.form.group.tab.properties.type'),
                    type: formData.type,
                    __: __
                }));

                this.delegateEvents();

                var options = {
                    allowClear: true,
                    ajax: {
                        url: Routing.generate('pim_enrich_grouptype_rest_index'),
                        quietMillis: 250,
                        cache: true,
                        data: function (term, page) {
                            return {
                                search: term,
                                options: {
                                    limit: 20,
                                    page: page,
                                    locale: UserContext.get('catalogLocale')
                                }
                            };
                        },
                        results: function (types) {
                            var data = {
                                more: 20 === Object.keys(types).length,
                                results: []
                            };
                            _.each(types, function (value) {
                                const { code, labels } = value

                                data.results.push({
                                    id: code,
                                    text: i18n.getLabel(labels, UserContext.get('catalogLocale'), code)
                                });
                            });

                            return data;
                        }
                    },
                    initSelection: function (element, callback) {
                        if (formData.type) {
                            FetcherRegistry.getFetcher('grouptype')
                                .fetch(formData.type)
                                .then(function (type) {
                                    callback({
                                        id: type.code,
                                        text: i18n.getLabel(
                                            type.labels,
                                            UserContext.get('catalogLocale'),
                                            type.code
                                        )
                                    });
                                });
                        }
                    }.bind(this)
                };

                initSelect2.init(this.$('[data-code="type"] input'), options);
            }
        });
    }
);
