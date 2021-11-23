import {useCallback, useEffect, useState} from 'react';
import {Axis} from '../model/catalog-volume';
import {Router, useRouter} from '@akeneo-pim-community/shared';

interface GetCatalogVolumeInterface {
  (router: Router): Promise<Axis[]>;
}

const useCatalogVolumeByAxis = (getCatalogVolume: GetCatalogVolumeInterface) => {
  const router = useRouter();
  const [axes, setAxes] = useState<Axis[]>([]);
  const fetchAxes = useCallback(async () => {
    const axisList = await getCatalogVolume(router);
    setAxes(axisList);
  }, [getCatalogVolume, router]);

  useEffect(() => {
    (async () => fetchAxes())();
  }, [fetchAxes]);

  return [axes] as const;
};

export {useCatalogVolumeByAxis};
export type {GetCatalogVolumeInterface};
