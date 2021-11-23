import {transformVolumesToAxis} from './catalogVolumeWrapper';
import {Router} from '@akeneo-pim-community/shared';
import {GetCatalogVolumeInterface} from '../hooks/useCatalogVolumeByAxis';
import {Axis} from 'feature/model/catalog-volume';

const getCatalogVolume: GetCatalogVolumeInterface = async (router: Router) => {
  try {
    const route = router.generate('pim_volume_monitoring_get_volumes');
    const response = await fetch(route);
    if (!response.ok) {
      return defaultResponse;
    }

    return await response
      .json()
      .then(catalogVolumesJson => {
        return transformVolumesToAxis(catalogVolumesJson);
      })
      .catch(error => {
        return defaultResponse;
      });
  } catch (e) {
    return defaultResponse;
  }
};

const defaultResponse: Axis[] | PromiseLike<Axis[]> = [];

export {getCatalogVolume};
