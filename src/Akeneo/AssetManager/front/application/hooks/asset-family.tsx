import {useState, useEffect} from 'react';
import {useSecurity} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamily, getAttributeAsMainMedia} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {AssetFamilyResult} from 'akeneoassetmanager/domain/fetcher/asset-family';

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

export const useAssetFamily = (
  assetFamilyIdentifier: AssetFamilyIdentifier | null
): {assetFamily: AssetFamily | null; rights: AssetFamilyRights} => {
  const {isGranted} = useSecurity();
  const assetFamilyFetcher = useAssetFamilyFetcher();
  const [assetFamily, setAssetFamily] = useState<AssetFamily | null>(null);
  const [assetFamilyPermission, setAssetFamilyPermission] = useState<AssetFamilyPermission | null>(null);

  useEffect(() => {
    if (null === assetFamilyIdentifier) return;

    assetFamilyFetcher.fetch(assetFamilyIdentifier).then((result: AssetFamilyResult) => {
      setAssetFamily(result.assetFamily);
      setAssetFamilyPermission(result.permission);
    });
  }, [assetFamilyIdentifier]);

  const attributeAsMainMedia = assetFamily ? getAttributeAsMainMedia(assetFamily) : null;
  const assetFamilyEdit =
    null !== assetFamily &&
    null !== assetFamilyPermission &&
    canEditAssetFamily(assetFamilyPermission, assetFamily.identifier);
  const assetCreate = isGranted('akeneo_assetmanager_asset_create') && assetFamilyEdit;

  const rights = {
    asset: {
      create: assetCreate,
      upload: assetCreate && null !== attributeAsMainMedia && attributeAsMainMedia.type === MEDIA_FILE_ATTRIBUTE_TYPE,
      edit: isGranted('akeneo_assetmanager_asset_edit') && assetFamilyEdit,
      delete:
        isGranted('akeneo_assetmanager_asset_edit') && isGranted('akeneo_assetmanager_asset_delete') && assetFamilyEdit,
    },
    assetFamily: {
      create: isGranted('akeneo_assetmanager_asset_family_create'),
      edit: isGranted('akeneo_assetmanager_asset_family_edit') && assetFamilyEdit,
    },
  };

  return {assetFamily, rights};
};
