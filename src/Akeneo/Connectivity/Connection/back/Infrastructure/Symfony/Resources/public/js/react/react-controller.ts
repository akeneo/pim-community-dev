import {ReactController as BaseReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';

/**
 * All the Connectivity controllers render in the same container, so that the React application is kept mounted while
 * navigating between Connectivity routes (see routeGuardToUnmount()).
 */
const container = document.createElement('div');

abstract class ReactController extends BaseReactController {
  getContainerRef(): Element {
    return container;
  }
}

export = ReactController;
