import {composeProviders, Index, RouterProvider, TranslateProvider} from '@akeneo-pim-ce/apps';
import * as $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

const BaseController = require('pim/controller/base');
const Router = require('pim/router');
const Mediator = require('oro/mediator');
const Translator = require('oro/translator');

const Providers = composeProviders(
  [RouterProvider, Router],
  [TranslateProvider, Translator]
);

class AppsController extends BaseController {
  renderRoute() {
    Mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    Mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    ReactDOM.render(
      <Providers>
        <Index />
      </Providers>,
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = AppsController;
