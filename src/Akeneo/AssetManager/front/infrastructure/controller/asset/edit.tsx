import $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import translate from 'akeneoassetmanager/tools/translator';
import AssetView from 'akeneoassetmanager/application/component/asset/edit';
import createStore from 'akeneoassetmanager/infrastructure/store';
import assetReducer from 'akeneoassetmanager/application/reducer/asset/edit';
import assetFetcher, {AssetResult} from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {assetEditionReceived} from 'akeneoassetmanager/domain/event/asset/edit';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
  localePermissionsChanged,
  assetFamilyPermissionChanged,
} from 'akeneoassetmanager/domain/event/user';
import {updateActivatedLocales} from 'akeneoassetmanager/application/action/locale';
import {updateChannels} from 'akeneoassetmanager/application/action/channel';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Key} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {getValueConfig} from 'akeneoassetmanager/application/configuration/value';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');
const fetcherRegistry = require('pim/fetcher-registry');
const router = require('pim/router');
const datagridState = require('pim/datagrid/state');
const {notify} = require('oro/messenger');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if (Key.Escape === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class AssetEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const promise = $.Deferred();

    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});
    $(window).on('beforeunload', this.beforeUnload);

    assetFetcher
      .fetch(
        denormalizeAssetFamilyIdentifier(route.params.assetFamilyIdentifier),
        denormalizeAssetCode(route.params.assetCode)
      )
      .then(async (assetResult: AssetResult) => {
        this.store = createStore(true, {router, datagridState, translate, notify, userContext})(assetReducer);
        await this.store.dispatch(updateChannels(fetcherRegistry.getFetcher('channel')) as any);
        this.store.dispatch(assetEditionReceived(assetResult.asset));
        this.store.dispatch(assetFamilyPermissionChanged(assetResult.permission));
        this.store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(updateActivatedLocales() as any);
        document.addEventListener('keydown', shortcutDispatcher(this.store));

        fetcherRegistry
          .getFetcher('locale-permission')
          .fetchAll()
          .then((localePermissions: LocalePermission[]) => {
            this.store.dispatch(localePermissionsChanged(localePermissions));
          });

        ReactDOM.render(
          <Provider store={this.store}>
            <DependenciesProvider>
              <ThemeProvider theme={pimTheme}>
                <ConfigProvider config={{value: getValueConfig()}}>
                  <AssetView />
                </ConfigProvider>
              </ThemeProvider>
            </DependenciesProvider>
          </Provider>,
          this.el
        );

        promise.resolve();
      })
      .catch(function(error: any) {
        if (error.request) {
          promise.reject(error.request);
        }

        throw error;
      });

    return promise.promise();
  }

  beforeUnload = () => {
    if (this.isDirty()) {
      return translate('pim_enrich.confirmation.discard_changes', {entity: 'asset'});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const message = translate('pim_enrich.confirmation.discard_changes', {entity: 'asset'});

    return this.isDirty() ? confirm(message) : true;
  }

  isDirty() {
    if (undefined === this.store) {
      return false;
    }
    const state = this.store.getState();

    return state.form.state.isDirty;
  }
}

export = AssetEditController;
