import {useState} from 'react';
import {Axis} from '../model/catalog-volume';
import {useRoute} from '@akeneo-pim-community/shared';

const useCatalogVolumeByAxis = (getCatalogVolumeInterface: any) => {
  const route = useRoute('pim_volume_monitoring_get_volumes');

  const [axis, setAxis] = useState<Axis[]>(getCatalogVolumeInterface());

  return [axis, setAxis] as const;
};

export {useCatalogVolumeByAxis};
