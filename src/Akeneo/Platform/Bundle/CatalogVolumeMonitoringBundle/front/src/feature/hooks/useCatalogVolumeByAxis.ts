import {useEffect, useState} from 'react';
import {Axis} from '../model/catalog-volume';
// TODO: import {useRoute} from '@akeneo-pim-community/shared';

interface GetCatalogVolumeInterface {
  (): Axis[];
}

const useCatalogVolumeByAxis = (getCatalogVolume: GetCatalogVolumeInterface) => {
  // TODO : const route = useRoute('pim_volume_monitoring_get_volumes');
  const [axes, setAxes] = useState<Axis[]>([]);
  useEffect(() => {
    setAxes(getCatalogVolume());
  }, [getCatalogVolume]);

  return [axes] as const;
};

export {useCatalogVolumeByAxis};
export type {GetCatalogVolumeInterface};
