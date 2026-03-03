import {transformVolumesToAxis} from './catalogVolumeWrapper';
import {Router} from '@akeneo-pim-community/shared';
import {GetCatalogVolumeInterface} from '../hooks/useCatalogVolumeByAxis';

const getCatalogVolume: GetCatalogVolumeInterface = async (router: Router) => {
  const route = router.generate('pim_volume_monitoring_get_volumes');
  const response = await fetch(route);
  if (!response.ok) {
    throw new Error(response.statusText);
  }

  return transformVolumesToAxis(await response.json());
};

export {getCatalogVolume};
