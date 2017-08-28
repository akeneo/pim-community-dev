'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/controller/base',
        'pim/form-builder',
        'pim/page-title',
        'pim/error',
        'routing'
    ],
    function ($, _, __, BaseController, FormBuilder, PageTitle, Error, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route, path) {
                var query = decodeURI(path.replace(route.route.tokens[0][1], '')).split('&filters')[0];

                var parameters = _.chain(query.split('&'))
                    .map(function (parameter) {
                        return parameter.split('=');
                    }).map(function (parameter) {
                        return {
                            key: parameter[0].replace('?', ''),
                            value: parameter[1]
                        };
                    }).value();

                var itemsCount = _.find(parameters, function (parameter) {
                    return 'itemsCount' === parameter.key;
                }).value;
                var actionName = _.find(parameters, function (parameter) {
                    return 'actionName' === parameter.key;
                }).value.replace(new RegExp('_', 'g'), '-');

                return $.ajax({
                    url: Routing.generate('pim_enrich_mass_edit_rest_get_filter') + query
                }).then(function (filters) {
                    return FormBuilder.build('pim-mass-' + actionName).then(function (form) {
                        form.setData({
                            filters: filters,
                            jobInstanceCode: null,
                            actions: [],
                            itemsCount: itemsCount
                        });

                        form.setElement(this.$el).render();
                    }.bind(this));
                }.bind(this));
            }
        });
    }
);
