import {useCallback, useEffect, useState} from 'react';
import {Axis} from '../model/catalog-volume';
import {Router, useRouter} from '@akeneo-pim-community/shared';

interface GetCatalogVolumeInterface {
  (router: Router): Promise<Axis[]>;
}

type FetchStatus = 'idle' | 'error' | 'fetching' | 'fetched';

const useCatalogVolumeByAxis = (getCatalogVolume: GetCatalogVolumeInterface) => {
  const router = useRouter();
  const [axes, setAxes] = useState<Axis[]>([]);
  const [status, setStatus] = useState<FetchStatus>('idle');

  const fetchAxes = useCallback(async () => {
    setStatus('fetching');
    try {
      const axisList = await getCatalogVolume(router);
      setAxes(axisList);
      setStatus('fetched');
    } catch (e) {
      setStatus('error');
    }
  }, [getCatalogVolume, router]);

  useEffect(() => {
    (async () => fetchAxes())();
  }, [fetchAxes]);

  return [axes, status] as const;
};

export {useCatalogVolumeByAxis};
export type {GetCatalogVolumeInterface};
