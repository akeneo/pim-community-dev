/* global define */
define(['oro/datagrid/navigate-action'],
function(NavigateAction) {
    'use strict';

    /**
     * Redirects to a specific tab
     *
     * @author  Antoine Guigan <antoine@akeneo.com>
     * @class   Pim.Datagrid.Action.ExportCollectionAction
     * @export  pim/datagrid/tab-redirect-action
     * @extends oro.datagrid.AbstractAction
     */
    var parent = NavigateAction.prototype,
        TabRedirectAction = NavigateAction.extend({
            run: function() {
                sessionStorage.redirectTab = this.tab;
                parent.run.call(this);
            }
        });
    return TabRedirectAction;
});
