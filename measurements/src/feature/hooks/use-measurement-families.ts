import {useCallback, useEffect, useState} from 'react';
import {MeasurementFamily} from '../model/measurement-family';
import {baseFetcher} from '../shared/fetcher/base-fetcher';
import {useRoute} from '@akeneo-pim-community/legacy';

const useMeasurementFamilies = (): [MeasurementFamily[] | null, () => Promise<void>] => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);
  const route = useRoute('pim_enrich_measures_rest_index');

  const fetchMeasurementFamilies = useCallback(async () => {
    setMeasurementFamilies(await baseFetcher(route));
  }, [route, setMeasurementFamilies]);

  useEffect(() => {
    (async () => fetchMeasurementFamilies())();
  }, [fetchMeasurementFamilies]);

  return [measurementFamilies, fetchMeasurementFamilies];
};

export {useMeasurementFamilies};
