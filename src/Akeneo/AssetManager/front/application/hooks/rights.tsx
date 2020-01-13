import * as React from 'react';
import {getAttributeAsMainMedia, AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamilyDataProvider} from 'akeneoassetmanager/application/hooks/asset-family';
const securityContext = require('pim/security-context');

type AssetFamilyRights = {
  asset: {
    upload: boolean;
    create: boolean;
    edit: boolean;
    deleteAll: boolean;
    delete: boolean;
  };
  assetFamily: {
    create: boolean;
    edit: boolean;
  };
};

export const useAssetFamilyRights = (
  dataProvider: AssetFamilyDataProvider,
  assetFamilyIdentifier: AssetFamilyIdentifier | null
): AssetFamilyRights => {
  //TODO clean this when using permission light models
  const [assetFamily, setAssetFamily] = React.useState<(AssetFamily & {permission: AssetFamilyPermission}) | null>(
    null
  );
  React.useEffect(() => {
    if (null === assetFamilyIdentifier) return;
    dataProvider.assetFamilyFetcher
      .fetch(assetFamilyIdentifier)
      .then(assetFamilyResult =>
        setAssetFamily({...assetFamilyResult.assetFamily, permission: assetFamilyResult.permission})
      );
  }, [assetFamilyIdentifier]);

  const attributeAsMainMedia = assetFamily ? getAttributeAsMainMedia(assetFamily) : null;
  const assetFamilyEdit = null !== assetFamily && canEditAssetFamily(assetFamily.permission, assetFamily.identifier);
  const assetCreate = securityContext.isGranted('akeneo_assetmanager_asset_create') && assetFamilyEdit;

  return {
    asset: {
      upload: null !== attributeAsMainMedia && assetCreate && attributeAsMainMedia.type === MEDIA_FILE_ATTRIBUTE_TYPE,
      create: assetCreate,
      edit: securityContext.isGranted('akeneo_assetmanager_asset_edit') && assetFamilyEdit,
      deleteAll:
        securityContext.isGranted('akeneo_assetmanager_asset_create') &&
        securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
        securityContext.isGranted('akeneo_assetmanager_assets_delete_all') &&
        assetFamilyEdit,
      delete:
        securityContext.isGranted('akeneo_assetmanager_asset_create') &&
        securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
        securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
        assetFamilyEdit,
    },
    assetFamily: {
      create: securityContext.isGranted('akeneo_assetmanager_asset_family_create'),
      edit: securityContext.isGranted('akeneo_assetmanager_asset_family_edit') && assetFamilyEdit,
    },
  };
};
