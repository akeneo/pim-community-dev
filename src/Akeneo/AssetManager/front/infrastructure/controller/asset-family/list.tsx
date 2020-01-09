import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import * as React from 'react';
import Library, {LibraryDataProvider} from 'akeneoassetmanager/application/component/library/library';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import assetAttributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const dataProvider: LibraryDataProvider = {
  assetFetcher,
  channelFetcher: {
    fetchAll: fetchAllChannels,
  },
  assetFamilyFetcher,
  assetAttributeFetcher: {
    fetchAll: assetAttributeFetcher.fetchAllNormalized,
  },
};

class AssetFamilyListController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});

    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Library
          dataProvider={dataProvider}
          initialContext={{locale: userContext.get('catalogLocale'), channel: userContext.get('catalogScope')}}
        />
      </ThemeProvider>,
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = AssetFamilyListController;
