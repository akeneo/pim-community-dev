'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing',
        'oro/navigation',
        'pim/dashboard/abstract-widget',
        'text!pimee/templates/dashboard/proposal-widget'
    ],
    function ($, _, Routing, Navigation, AbstractWidget, widgetTemplate) {
        return AbstractWidget.extend({
            tagName: 'table',

            id: 'proposal-widget',

            className: 'table table-condensed table-light groups unspaced',

            options: {
                contentLoaded: false
            },

            template: _.template(widgetTemplate),

            events: {
                'click .product-label': 'followLink',
                'click .product-review': 'productReview'
            },

            followLink: function (e) {
                e.preventDefault();

                if ($(e.currentTarget).data('redirecttab')) {
                    sessionStorage.setItem('redirectTab', $(e.currentTarget).data('redirecttab'));
                }

                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pim_enrich_product_edit',
                        { id: $(e.currentTarget).data('id') }
                    )
                );
            },

            productReview: function (e) {
                e.preventDefault();
                Navigation.getInstance().setLocation($(e.currentTarget).data('product-review-url'));
            },

            _processResponse: function (data) {
                this.options.contentLoaded = true;

                return data;
            }
        });
    }
);
