import {Marketplace, MarketplaceRoutes} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

class MarketplaceController extends ReactController {
  reactElementToMount() {
    return <Marketplace dependencies={dependencies} routes={MarketplaceRoutes} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_connect_marketplace/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute(route: {name: string}) {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-connect'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-connect-marketplace'});

    return super.renderRoute(route);
  }
}

export = MarketplaceController;
