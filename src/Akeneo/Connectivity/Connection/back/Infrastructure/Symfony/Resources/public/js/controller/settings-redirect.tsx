import React from 'react';
import ReactController from '../react/react-controller';
import {dependencies} from '../dependencies';
import {RedirectConnectionSettingsToConnectMenu} from '@akeneo-pim-community/connectivity-connection';

const mediator = require('oro/mediator');

class AuditRedirectController extends ReactController {
    reactElementToMount() {
        return <RedirectConnectionSettingsToConnectMenu dependencies={dependencies}/>;
    }

    routeGuardToUnmount() {
        return /^akeneo_connectivity_connection_settings_redirect$/;
    }

    initialize() {
        this.$el.addClass('AknConnectivityConnection-view');

        return super.initialize();
    }

    renderRoute(route: any) {
        mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
        mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-connection-settings'});

        return super.renderRoute(route);
    }
}

export default AuditRedirectController;
