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
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param product
             */
            _onAjaxSuccess: function (product) {
                this.datagrid.hideLoading();
                this.datagrid.collection.fetch();

                mediator.trigger('product:action:proposal:post_reject:success', product);
            }
        });
    }
);
