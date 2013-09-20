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
        initialize: function(options) {
            parent.initialize.call(this, options);
            if (!options.tab) {
                throw new TypeError("'tab' is required");
            }
            this.tab = options.tab;
        }, 
        execute: function() {
            sessionStorage.activeTab = this.tab;
            parent.execute.call(this);
        }
    });
    return TabRedirectAction;
});
