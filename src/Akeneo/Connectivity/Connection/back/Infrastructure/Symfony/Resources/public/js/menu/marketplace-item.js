'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/menu/item',
    'routing',
    'pim/menu/connectivity/connection/templates/marketplace-item',
], function (_, __, Item, Routing, template) {
    return Item.extend({
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.$el.empty().append(
                this.template({
                    title: this.getLabel(),
                    url: Routing.generateHash(this.getRoute(), this.getRouteParams()),
                    active: this.active,
                    new_label: __('pim_menu.item.new_label'),
                })
            );

            this.delegateEvents();
        },
    });
});
