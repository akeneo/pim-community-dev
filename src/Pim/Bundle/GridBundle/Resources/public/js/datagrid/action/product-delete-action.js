/* global define */
define(['oro/datagrid/delete-action', 'oro/translator', 'oro/navigation', 'jquery'],
function(DeleteAction, __, Navigation, $) {
    'use strict';

    /**
     * Deletes a product and refreshes category tree
     * 
     * @author  Antoine Guigan <antoine@akeneo.com>
     * @class   Pim.Datagrid.Action.ExportCollectionAction
     * @export  pim/datagrid/tab-redirect-action
     * @extends oro.datagrid.AbstractAction
     */
    var ProductDeleteAction = DeleteAction.extend({
            /**
             * Confirm delete item
             */
            doDelete: function() {
                var self = this;
                $.ajax(
                    this.getLink(),
                    {
                        type: 'delete',
                        error: function() {
                            self.getErrorDialog().open();
                        },

                        success: function() {
                            (function(navigation, messageText) {
                                navigation.addFlashMessage('success', messageText);
                                navigation.refreshPage()
                            })(Navigation.getInstance(), __('Item deleted'))
                        }
                    }
                )
            }

        });
    return ProductDeleteAction;
});
