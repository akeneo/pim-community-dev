import $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetFamilyEdit} from 'akeneoassetmanager/application/component/asset-family/edit';
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
import {updateActivatedLocales} from 'akeneoassetmanager/application/action/locale';
import {updateChannels} from 'akeneoassetmanager/application/action/channel';
import {PermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {permissionEditionReceived} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {gridStateStoragePath} from 'akeneoassetmanager/infrastructure/middleware/grid';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ThemeProvider} from 'styled-components';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {pimTheme, Key} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');
const fetcherRegistry = require('pim/fetcher-registry');
const router = require('pim/router');
const Routing = require('routing');

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
        this.store.dispatch(attributeListUpdated(assetFamilyResult.attributes) as any);
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
            <DependenciesProvider>
              <ThemeProvider theme={pimTheme}>
                <AssetFamilyEdit
                  initialTab={route.params.tab}
                  onTabChange={(tabCode: string) => {
                    const route = router.match(window.location.hash);
                    if (undefined !== route.params.tab) {
                      history.replaceState(
                        null,
                        '',
                        '#' + Routing.generate(route.name, {...route.params, tab: tabCode})
                      );
                    }
                  }}
                />
              </ThemeProvider>
            </DependenciesProvider>
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
      const state = this.store.getState();
      const assetFamilyLabel = getAssetFamilyLabel(state.form.data, state.user.catalogLocale);

      return __('pim_asset_manager.asset_family.edit.discard_changes', {assetFamilyLabel});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const state = this.store.getState();
    const assetFamilyLabel = getAssetFamilyLabel(state.form.data, state.user.catalogLocale);
    const message = __('pim_asset_manager.asset_family.edit.discard_changes', {assetFamilyLabel});

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
