'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/page-title',
        'routing'
    ],
    function ($, _, __, BaseController, FormBuilder, PageTitle, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route, path) {
                var query = path.replace(route.route.tokens[0][1], '');
                var parameters = _.chain(query.split('&'))
                    .map(function (parameter) {
                        return parameter.split('=');
                    }).map(function (parameter) {
                        return {
                            key: parameter[0].replace('?', ''),
                            value: parameter[1]
                        };
                    }).value();

                var actionName = _.find(parameters, function (parameter) {
                    return 'actionName' === parameter.key;
                }).value.replace(new RegExp('_', 'g'), '-');

                return $.ajax({
                    url: Routing.generate('pim_enrich_mass_edit_rest_get_filter') + query
                }).then((response) => {
                    const filters = response.filters;
                    const itemsCount = response.itemsCount;

                    return FormBuilder.build('pim-mass-' + actionName).then((form) => {
                        form.setData({
                            filters: filters,
                            jobInstanceCode: null,
                            actions: [],
                            itemsCount: itemsCount
                        });

                        form.setElement(this.$el).render();

                        return form;
                    });
                });
            }
        });
    }
);
