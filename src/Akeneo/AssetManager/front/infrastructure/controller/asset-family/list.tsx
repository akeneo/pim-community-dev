import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import * as React from 'react';
import createStore from 'akeneoassetmanager/infrastructure/store';
import assetFamilyReducer from 'akeneoassetmanager/application/reducer/asset-family/index';
import {updateAssetFamilyResults} from 'akeneoassetmanager/application/action/asset-family/search';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
} from 'akeneoassetmanager/domain/event/user';
import Key from 'akeneoassetmanager/tools/key';
import Library from 'akeneoassetmanager/application/library/component/library';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {redirectToAsset} from 'akeneoassetmanager/application/action/asset/router';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if (Key.Escape === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class AssetFamilyListController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});

    const store = createStore(true)(assetFamilyReducer);
    store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
    store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
    store.dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
    store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
    store.dispatch(updateAssetFamilyResults());
    document.addEventListener('keydown', shortcutDispatcher(store));

    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <Library
          initialContext={{locale: store.getState().user.catalogLocale, channel: store.getState().user.catalogChannel}}
          redirectToAsset={(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => {
            store.dispatch(redirectToAsset(assetFamilyIdentifier, assetCode));
          }}
        ></Library>
      </ThemeProvider>,
      this.el
    );

    return $.Deferred().resolve();
  }

  beforeUnload = () => {
    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };
}

export = AssetFamilyListController;
