'use strict';

/**
 * Reject proposal action
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    ['oro/mediator', 'oro/datagrid/ajax-action'],
    function (mediator, AjaxAction) {
        return AjaxAction.extend({
            /**
             * @inheritdoc
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param product
             */
            _onAjaxSuccess: function (product) {
                this.datagrid.collection.fetch();

                mediator.trigger('pim_enrich:form:proposal:post_reject:success', product);
            }
        });
    }
);
