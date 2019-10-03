import * as $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

import {Index, RouterProvider} from '@akeneo-pim-ce/apps';

const BaseController = require('pim/controller/base');
const Mediator = require('oro/mediator');
const Router = require('pim/router');

const provideServices = (Component: any) => (props: any) => (
  <RouterProvider value={Router}>
    <Component {...props} />
  </RouterProvider>
);

const IndexWithServices = provideServices(Index);

class AppsController extends BaseController {
  renderRoute() {
    Mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    Mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    ReactDOM.render(<IndexWithServices />, this.el);

    return $.Deferred().resolve();
  }
}

export = AppsController;
