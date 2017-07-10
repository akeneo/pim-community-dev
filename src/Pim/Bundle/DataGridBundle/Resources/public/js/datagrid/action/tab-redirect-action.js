/* global define */
import NavigateAction from 'oro/datagrid/navigate-action';
    

    /**
     * Redirects to a specific tab
     *
     * @author  Antoine Guigan <antoine@akeneo.com>
     * @class   Pim.Datagrid.Action.TabRedirectAction
     * @export  pim/datagrid/tab-redirect-action
     * @extends oro.datagrid.AbstractAction
     */
    var parent = NavigateAction.prototype,
        TabRedirectAction = NavigateAction.extend({
            useDirectLauncherLink: false,
            run: function() {
                sessionStorage.redirectTab = '#' + this.tab;
                parent.run.call(this);
            }
        });
    export default TabRedirectAction;

