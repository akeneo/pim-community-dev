import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import * as React from 'react';
import Library, {LibraryDataProvider} from 'akeneoassetmanager/application/component/library/library';
import {ThemeProvider} from 'styled-components';
import {fetchChannels} from 'akeneoassetmanager/infrastructure/fetcher/channel';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {getConfig} from 'pimui/js/config-registry';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');
const fetcherRegistry = require('pim/fetcher-registry');

const dataProvider: LibraryDataProvider = {
  channelFetcher: {
    fetchAll: fetchChannels(fetcherRegistry.getFetcher('channel')),
  },
};

class AssetFamilyListController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ConfigProvider
            config={{
              value: getConfig('akeneoassetmanager/application/configuration/value') ?? {},
              sidebar: getConfig('akeneoassetmanager/application/configuration/sidebar') ?? {},
              attribute: getConfig('akeneoassetmanager/application/configuration/attribute') ?? {},
            }}
          >
            <Library
              dataProvider={dataProvider}
              initialContext={{locale: userContext.get('catalogLocale'), channel: userContext.get('catalogScope')}}
            />
          </ConfigProvider>
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );

    return $.Deferred().resolve();
  }
}

export = AssetFamilyListController;
