import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamily, getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {AssetFamilyResult} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetFamilyFetcher} from 'akeneoassetmanager/domain/fetcher/asset-family';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import securityContext from 'akeneoassetmanager/tools/security-context';

type AssetFamilyRights = {
  asset: {
    upload: boolean;
    create: boolean;
    edit: boolean;
    delete: boolean;
  };
  assetFamily: {
    create: boolean;
    edit: boolean;
  };
};

export type AssetFamilyDataProvider = {
  assetFamilyFetcher: AssetFamilyFetcher;
};

export const useAssetFamily = (
  dataProvider: AssetFamilyDataProvider,
  assetFamilyIdentifier: AssetFamilyIdentifier | null
): {assetFamily: AssetFamily | null; rights: AssetFamilyRights} => {
  const [assetFamily, setAssetFamily] = React.useState<AssetFamily | null>(null);
  const [assetFamilyPermission, setAssetFamilyPermission] = React.useState<AssetFamilyPermission | null>(null);
  React.useEffect(() => {
    if (null === assetFamilyIdentifier) return;
    dataProvider.assetFamilyFetcher.fetch(assetFamilyIdentifier).then((result: AssetFamilyResult) => {
      setAssetFamily(result.assetFamily);
      setAssetFamilyPermission(result.permission);
    });
  }, [assetFamilyIdentifier]);

  const attributeAsMainMedia = assetFamily ? getAttributeAsMainMedia(assetFamily) : null;
  const assetFamilyEdit =
    null !== assetFamily &&
    null !== assetFamilyPermission &&
    canEditAssetFamily(assetFamilyPermission, assetFamily.identifier);
  const assetCreate = securityContext.isGranted('akeneo_assetmanager_asset_create') && assetFamilyEdit;

  const rights = {
    asset: {
      create: assetCreate,
      upload: assetCreate && null !== attributeAsMainMedia && attributeAsMainMedia.type === MEDIA_FILE_ATTRIBUTE_TYPE,
      edit: securityContext.isGranted('akeneo_assetmanager_asset_edit') && assetFamilyEdit,
      delete:
        securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
        securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
        assetFamilyEdit,
    },
    assetFamily: {
      create: securityContext.isGranted('akeneo_assetmanager_asset_family_create'),
      edit: securityContext.isGranted('akeneo_assetmanager_asset_family_edit') && assetFamilyEdit,
    },
  };

  return {assetFamily, rights};
};
