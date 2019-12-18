import $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoassetmanager/tools/translator';
import AssetFamilyView from 'akeneoassetmanager/application/component/asset-family/edit';
import createStore from 'akeneoassetmanager/infrastructure/store';
import assetFamilyReducer from 'akeneoassetmanager/application/reducer/asset-family/edit';
import assetFamilyFetcher, {AssetFamilyResult} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import permissionFetcher from 'akeneoassetmanager/infrastructure/fetcher/permission';
import {
  assetFamilyEditionReceived,
  assetFamilyAssetCountUpdated,
} from 'akeneoassetmanager/domain/event/asset-family/edit';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  localePermissionsChanged,
  uiLocaleChanged,
  assetFamilyPermissionChanged,
} from 'akeneoassetmanager/domain/event/user';
import {setUpSidebar} from 'akeneoassetmanager/application/action/sidebar';
import {updateActivatedLocales} from 'akeneoassetmanager/application/action/locale';
import {updateCurrentTab} from 'akeneoassetmanager/application/event/sidebar';
import {updateChannels} from 'akeneoassetmanager/application/action/channel';
import {attributeListGotUpdated} from 'akeneoassetmanager/application/action/attribute/list';
import {PermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {permissionEditionReceived} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {restoreFilters} from 'akeneoassetmanager/application/action/asset/search';
import {gridStateStoragePath} from 'akeneoassetmanager/infrastructure/middleware/grid';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import Key from 'akeneoassetmanager/tools/key';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');
const fetcherRegistry = require('pim/fetcher-registry');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if (Key.Escape === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class AssetFamilyEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const promise = $.Deferred();

    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});
    $(window).on('beforeunload', this.beforeUnload);

    assetFamilyFetcher
      .fetch(denormalizeAssetFamilyIdentifier(route.params.identifier))
      .then(async (assetFamilyResult: AssetFamilyResult) => {
        this.store = createStore(true)(assetFamilyReducer);
        const assetFamilyIdentifier = assetFamilyResult.assetFamily.identifier;
        const filters = this.getFilters(assetFamilyIdentifier);

        permissionFetcher.fetch(assetFamilyResult.assetFamily.identifier).then((permissions: PermissionCollection) => {
          this.store.dispatch(permissionEditionReceived(permissions));
        });

        // Not idea, maybe we should discuss about it
        await this.store.dispatch(updateChannels() as any);
        this.store.dispatch(updateActivatedLocales() as any);
        this.store.dispatch(assetFamilyEditionReceived(assetFamilyResult.assetFamily));
        this.store.dispatch(assetFamilyAssetCountUpdated(assetFamilyResult.assetCount));
        this.store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(setUpSidebar('akeneo_asset_manager_asset_family_edit') as any);
        this.store.dispatch(updateCurrentTab(route.params.tab));
        this.store.dispatch(restoreFilters(filters) as any);
        this.store.dispatch(attributeListGotUpdated(assetFamilyResult.attributes) as any);
        this.store.dispatch(assetFamilyPermissionChanged(assetFamilyResult.permission));

        document.addEventListener('keydown', shortcutDispatcher(this.store));

        fetcherRegistry
          .getFetcher('locale-permission')
          .fetchAll()
          .then((localePermissions: LocalePermission[]) => {
            this.store.dispatch(localePermissionsChanged(localePermissions));
          });

        ReactDOM.render(
          <Provider store={this.store}>
            <ThemeProvider theme={akeneoTheme}>
              <AssetFamilyView />
            </ThemeProvider>
          </Provider>,
          this.el
        );

        promise.resolve();
      })
      .catch((error: any) => {
        if (error.request) {
          promise.reject(error.request);
        }

        throw error;
      });

    return promise.promise();
  }

  getFilters = (assetFamilyIdentifier: string): Filter[] => {
    return null !== sessionStorage.getItem(`${gridStateStoragePath}.${assetFamilyIdentifier}`)
      ? JSON.parse(sessionStorage.getItem(`${gridStateStoragePath}.${assetFamilyIdentifier}`) as string)
      : [];
  };

  beforeUnload = () => {
    if (this.isDirty()) {
      return __('pim_enrich.confirmation.discard_changes', {entity: 'asset family'});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const message = __('pim_enrich.confirmation.discard_changes', {entity: 'asset family'});

    return this.isDirty() ? confirm(message) : true;
  }

  isDirty() {
    if (undefined === this.store) {
      return false;
    }

    const state = this.store.getState();

    return (
      state.form.state.isDirty || state.attribute.isDirty || state.options.isDirty || state.permission.state.isDirty
    );
  }
}

export = AssetFamilyEditController;
