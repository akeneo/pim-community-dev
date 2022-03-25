import {LocalePermission} from 'akeneoassetmanager/domain/model/permission/locale';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import AssetFamilyIdentifier, {
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {isIdentifier} from 'akeneoassetmanager/domain/model/identifier';

export interface RightState {
  locale: LocalePermission[];
  assetFamily: AssetFamilyPermission;
}

export default (
  state: RightState = {
    locale: [],
    assetFamily: {assetFamilyIdentifier: '', edit: false},
  },
  action: {
    type: string;
    localePermissions: LocalePermission[];
    assetFamilyPermission: AssetFamilyPermission;
  }
): RightState => {
  switch (action.type) {
    case 'LOCALE_PERMISSIONS_CHANGED':
      state = {...state, locale: action.localePermissions};
      break;
    case 'ASSET_FAMILY_PERMISSIONS_CHANGED':
      state = {...state, assetFamily: action.assetFamilyPermission};
      break;
    default:
      break;
  }

  return state;
};

export const canEditLocale = (localesPermission: LocalePermission[], currentLocale: string) => {
  const localePermission = localesPermission.find((localePermission: LocalePermission) => {
    return localePermission.code === currentLocale;
  });

  if (undefined === localePermission) {
    return false;
  }

  return localePermission.edit;
};

export const canEditAssetFamily = (
  assetFamilyPermission: AssetFamilyPermission,
  assetFamilyIdentifier: AssetFamilyIdentifier
) => {
  if (!isIdentifier(assetFamilyPermission.assetFamilyIdentifier) || !isIdentifier(assetFamilyIdentifier)) {
    return false;
  }

  if (!assetFamilyidentifiersAreEqual(assetFamilyPermission.assetFamilyIdentifier, assetFamilyIdentifier)) {
    return false;
  }

  return assetFamilyPermission.edit;
};
