import {MarketplaceSettings} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import highlightMenu from '../menu/highlight-menu';
import ReactController from '../react/react-controller';

class MarketplaceController extends ReactController {
  reactElementToMount() {
    return <MarketplaceSettings dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_connect_marketplace/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute(route: {name: string}) {
    highlightMenu(route.name);

    return super.renderRoute(route);
  }
}

export = MarketplaceController;
