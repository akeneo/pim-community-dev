import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import * as React from 'react';
import Library from 'akeneoassetmanager/application/library/component/library';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

class AssetFamilyListController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});

    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Library
          initialContext={{locale: userContext.get('catalogLocale'), channel: userContext.get('catalogScope')}}
        />
      </ThemeProvider>,
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = AssetFamilyListController;
