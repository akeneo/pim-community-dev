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
            initialize: function (options) {
                this.config = options.config;
            },

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

                /*
                 The `values` parameter can raise a 414 Too Long URI when we select more than 650 products in
                 the grid. We use POST request to send the values to the backend to avoid these exceptions.
                 */
                const values = _.find(parameters, function (parameter) {
                    return 'values' === parameter.key;
                }).value.split('%2C'); // %2C = ,
                const queryWithoutValues = query.replace(/&values=[^&]+/, '');

                return $.ajax({
                    url: Routing.generate(this.config.route) + queryWithoutValues,
                    method: 'POST',
                    data: { values }
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
