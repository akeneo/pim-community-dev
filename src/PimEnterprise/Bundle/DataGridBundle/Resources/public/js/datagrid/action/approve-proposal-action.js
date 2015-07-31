'use strict';
/**
 * Approve proposal action
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

                mediator.trigger('product:action:proposal:post_approve:success', product);
            },

            /**
             * Override the default handler to avoid displaying the error modal and triggering our own event instead
             *
             * @param jqXHR
             */
            _onAjaxError: function (jqXHR) {
                this.datagrid.hideLoading();

                mediator.trigger('product:action:proposal:post_approve:error', jqXHR.responseJSON.message);
            }
        });
    }
);
