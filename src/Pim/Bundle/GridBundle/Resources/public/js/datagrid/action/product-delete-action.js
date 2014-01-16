/* global define */
define(['oro/grid/delete-action', 'oro/navigation', 'jquery', 'underscore', 'oro/translator'],
function(DeleteAction, Navigation, $, _, __) {
    'use strict';

    /**
     * Deletes a product and refreshes category tree
     *
     * @author  Antoine Guigan <antoine@akeneo.com>
     * @class   Pim.Datagrid.Action.ProductDeleteAction
     * @export  pim/datagrid/tab-redirect-action
     * @extends oro.datagrid.AbstractAction
     */
    var ProductDeleteAction = DeleteAction.extend({
        /**
         * Confirm delete item
         */
        doDelete: function() {
            var self = this,
                collection = self.datagrid.collection;
            $.ajax(
                this.getLink(),
                {
                    type: 'delete',
                    error: function() {
                        self.getErrorDialog().open();
                    },

                    success: function() {
                        (function(navigation, stateData, messageText) {
                            navigation.addFlashMessage('success', messageText);
                            navigation.navigate(
                                'url=' + navigation.url.split('?').shift() + '|g/' +
                                stateData + '&boost=' + new Date().getTime(),
                                {trigger: true}
                            );
                        })(
                            Navigation.getInstance(),
                            collection.encodeStateData(collection.state),
                            __('Item deleted'));
                    }
                }
            );
        }
    });

    return ProductDeleteAction;
});
