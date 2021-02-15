import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import AssetFamilyIdentifier, {denormalizeAssetFamilyIdentifier} from '../../../../../domain/model/asset-family/identifier';
import {Query} from '../../../../../domain/fetcher/fetcher';
import {normalizeUpdaterCollection} from '../model/updater';
import {useRouter} from '@akeneo-pim-community/legacy-bridge/src';

const useMassEdit = () => {
  const router = useRouter();

  const validate = async (assetFamilyIdentifier: AssetFamilyIdentifier, query: Query, updaterCollection: Updater[]) => {
    const url = router.generate('akeneo_asset_manager_asset_validate_mass_edit_rest', {
      assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
    });

    const normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    const response = await fetch(url, {
      method: 'POST',
      body: JSON.stringify({
        type: 'edit',
        query: query,
        updaters: normalizedUpdaterCollection,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    return await response.json();
  };

  const launch = async (assetFamilyIdentifier: AssetFamilyIdentifier, query: Query, updaterCollection: Updater[]) => {
    const url = router.generate('akeneo_asset_manager_asset_mass_edit_rest', {
      assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
    });

    const normalizedUpdaterCollection = normalizeUpdaterCollection(updaterCollection);

    const response = await fetch(url, {
      method: 'POST',
      body: JSON.stringify({
        type: 'edit',
        query: query,
        updaters: normalizedUpdaterCollection,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    await response.json();
  };

  return [
    validate,
    launch,
  ] as const;
};

export {useMassEdit};
