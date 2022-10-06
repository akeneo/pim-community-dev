import $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider, useDispatch} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import translate from 'akeneoassetmanager/tools/translator';
import AssetView from 'akeneoassetmanager/application/component/asset/edit';
import createStore from 'akeneoassetmanager/infrastructure/store';
import assetReducer from 'akeneoassetmanager/application/reducer/asset/edit';
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
import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {ThemeProvider} from 'styled-components';
import {useBooleanState, pimTheme, Key} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {getConfig} from 'pimui/js/config-registry';
import {useAssetFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFetcher';
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

type AssetLoaderProps = {
  assetFamilyIdentifier: string;
  assetCode: string;
  children: ReactNode;
};

type LoadingError = {
  statusCode: number;
  statusText: string;
};

const AssetLoader = ({assetFamilyIdentifier, assetCode, children}: AssetLoaderProps) => {
  const assetFetcher = useAssetFetcher();
  const dispatch = useDispatch();
  const [assetFetched, assetIsFetched] = useBooleanState(false);
  const [error, setError] = useState<LoadingError | null>(null);

  useEffect(() => {
    (async () => {
      try {
        const assetResult = await assetFetcher.fetch(assetFamilyIdentifier, assetCode);
        dispatch(assetEditionReceived(assetResult.asset));
        dispatch(assetFamilyPermissionChanged(assetResult.permission));
        dispatch(await updateChannels(fetcherRegistry.getFetcher('channel')));
        await updateActivatedLocales(fetcherRegistry.getFetcher('locale'));
        dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
        dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        assetIsFetched();
      } catch (error) {
        setError({
          statusCode: error.response.status,
          statusText: error.response.statusText,
        });
      }
    })();
  }, [assetFetcher]);

  if (error !== null) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: error.statusCode.toString()})}
        code={error.statusCode}
        message={error.statusText}
      />
    );
  }

  return <>{assetFetched && children}</>;
};

class AssetEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-asset-family'});
    $(window).on('beforeunload', this.beforeUnload);

    this.store = createStore(true, {router, datagridState, translate, notify, userContext})(assetReducer);

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
              <AssetLoader
                assetFamilyIdentifier={route.params.assetFamilyIdentifier as string}
                assetCode={route.params.assetCode as string}
              >
                <AssetView />
              </AssetLoader>
            </ConfigProvider>
          </ThemeProvider>
        </DependenciesProvider>
      </Provider>,
      this.el
    );

    return $.Deferred().resolve();
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
