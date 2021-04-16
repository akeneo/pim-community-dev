import React from 'react';
import {ReactController} from '@akeneo-pim-community/shared/src/bridge/react';
import {SystemInfoApp} from '../SystemInfoApp';

const mediator = require('oro/mediator');

class SystemInfoController extends ReactController {
  reactElementToMount() {
    return <SystemInfoApp />;
  }

  routeGuardToUnmount() {
    return /pim_analytics_system_info_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-info'});

    return super.renderRoute();
  }
}

export = SystemInfoController;
