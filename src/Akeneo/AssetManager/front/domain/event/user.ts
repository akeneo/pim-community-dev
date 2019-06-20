import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';

export const defaultCatalogLocaleChanged = (locale: string) => {
  return {type: 'DEFAULT_LOCALE_CHANGED', locale, target: 'defaultCatalog'};
};

export const catalogLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'catalog'};
};

export const uiLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'ui'};
};

export const catalogChannelChanged = (channel: string) => (dispatch: any, getState: any) => {
  const channels = undefined === getState().structure ? [] : getState().structure.channels;

  dispatch({type: 'CHANNEL_CHANGED', channel, target: 'catalog', channels});
};

export const localePermissionsChanged = (localePermissions: LocalePermission[]) => {
  return {type: 'LOCALE_PERMISSIONS_CHANGED', localePermissions};
};

export const assetFamilyPermissionChanged = (assetFamilyPermission: AssetFamilyPermission) => {
  return {type: 'ASSET_FAMILY_PERMISSIONS_CHANGED', assetFamilyPermission};
};
