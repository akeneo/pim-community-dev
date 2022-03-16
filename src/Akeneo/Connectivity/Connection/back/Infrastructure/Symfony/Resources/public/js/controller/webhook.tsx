import {WebhookSettings} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import highlightMenu from '../menu/highlight-menu';
import ReactController from '../react/react-controller';

class WebhookController extends ReactController {
  reactElementToMount() {
    return <WebhookSettings dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_webhook/;
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

export default WebhookController;
