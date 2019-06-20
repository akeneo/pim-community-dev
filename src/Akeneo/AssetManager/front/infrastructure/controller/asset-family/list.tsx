import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import AssetFamilyView from 'akeneoassetmanager/application/component/asset-family/index';
import createStore from 'akeneoassetmanager/infrastructure/store';
import assetFamilyReducer from 'akeneoassetmanager/application/reducer/asset-family/index';
import {updateAssetFamilyResults} from 'akeneoassetmanager/application/action/asset-family/search';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
} from 'akeneoassetmanager/domain/event/user';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if ('Escape' === event.code) {
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
      <Provider store={store}>
        <AssetFamilyView />
      </Provider>,
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
