import $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider, useDispatch} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import translate from 'akeneoassetmanager/tools/translator';
import createStore from 'akeneoassetmanager/infrastructure/store';
import {createAssetFamilyReducer} from 'akeneoassetmanager/application/reducer/asset-family/edit';
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
import {permissionEditionReceived} from 'akeneoassetmanager/domain/event/asset-family/permission';
import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {gridStateStoragePath} from 'akeneoassetmanager/infrastructure/middleware/grid';
import {ThemeProvider} from 'styled-components';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {pimTheme, Key} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {AssetFamilyEdit} from 'akeneoassetmanager/application/component/asset-family/edit';
import {getConfig} from 'pimui/js/config-registry';
import {AttributeConfig, getReducer} from 'akeneoassetmanager/application/configuration/attribute';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';
import {ReactNode, useEffect, useState} from 'react';
import {FullScreenError} from '@akeneo-pim-community/shared';
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

type AssetFamilyLoaderProps = {
  assetFamilyIdentifier: string;

  children: ReactNode;
};

type LoadingError = {
  statusCode: number;
  statusText: string;
};

const AssetFamilyLoader = ({assetFamilyIdentifier, children}: AssetFamilyLoaderProps) => {
  const assetFamilyFetcher = useAssetFamilyFetcher();
  const dispatch = useDispatch();
  const [assetFamilyIsFetched, setAssetFamilyIsFetched] = useState(false);
  const [error, setError] = useState<LoadingError | null>(null);

  useEffect(() => {
    (async () => {
      try {
        const assetFamilyResult = await assetFamilyFetcher.fetch(assetFamilyIdentifier);
        const permissions = await permissionFetcher.fetch(assetFamilyResult.assetFamily.identifier);
        dispatch(assetFamilyEditionReceived(assetFamilyResult.assetFamily));
        dispatch(assetFamilyAssetCountUpdated(assetFamilyResult.assetCount));
        dispatch(attributeListUpdated(assetFamilyResult.attributes));
        dispatch(assetFamilyPermissionChanged(assetFamilyResult.permission));
        dispatch(permissionEditionReceived(permissions));
        dispatch(await updateChannels(fetcherRegistry.getFetcher('channel')) as any);
        dispatch(updateActivatedLocales(fetcherRegistry.getFetcher('locale')) as any);
        dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
        dispatch(uiLocaleChanged(userContext.get('uiLocale')));

        setAssetFamilyIsFetched(true);
      } catch (error) {
        setError({
          statusCode: error.response.status,
          statusText: error.response.statusText,
        });
      }
    })();
  }, [assetFamilyFetcher]);

  if (error !== null) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: error.statusCode.toString()})}
        code={error.statusCode}
        message={error.statusText}
      />
    );
  }

  return <>{assetFamilyIsFetched && children}</>;
};

class AssetFamilyEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const attributeConfig = getConfig('akeneoassetmanager/application/configuration/attribute') as AttributeConfig;
    const reducer = getReducer(attributeConfig);

    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});
    $(window).on('beforeunload', this.beforeUnload);
    this.store = createStore(true, {router, datagridState, translate, notify, userContext})(
      createAssetFamilyReducer(reducer)
    );

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
            <ConfigProvider
              config={{
                value: getConfig('akeneoassetmanager/application/configuration/value') ?? {},
                sidebar: getConfig('akeneoassetmanager/application/configuration/sidebar') ?? {},
                attribute: getConfig('akeneoassetmanager/application/configuration/attribute') ?? {},
              }}
            >
              <AssetFamilyLoader assetFamilyIdentifier={route.params.identifier}>
                <AssetFamilyEdit
                  initialTab={route.params.tab}
                  onTabChange={(tabCode: string) => {
                    const route = router.match(window.location.hash);
                    if (undefined !== route.params.tab) {
                      history.replaceState(
                        null,
                        '',
                        '#' + router.generate(route.name, {...route.params, tab: tabCode})
                      );
                    }
                  }}
                />
              </AssetFamilyLoader>
            </ConfigProvider>
          </ThemeProvider>
        </DependenciesProvider>
      </Provider>,
      this.el
    );

    return $.Deferred().resolve();
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

      return translate('pim_asset_manager.asset_family.edit.discard_changes', {assetFamilyLabel});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const state = this.store.getState();
    const assetFamilyLabel = getAssetFamilyLabel(state.form.data, state.user.catalogLocale);
    const message = translate('pim_asset_manager.asset_family.edit.discard_changes', {assetFamilyLabel});

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
