import {useCallback, useEffect, useState} from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {baseFetcher} from 'akeneomeasure/shared/fetcher/base-fetcher';
import {useRoute} from '@akeneo-pim-community/legacy-bridge';

const useMeasurementFamilies = (): [MeasurementFamily[] | null, () => Promise<void>] => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);
  const route = useRoute('pim_enrich_measures_rest_index');

  const fetchMeasurementFamilies = useCallback(async () => {
    setMeasurementFamilies(await baseFetcher(route));
  }, [route, setMeasurementFamilies]);

  useEffect(() => {
    (async () => fetchMeasurementFamilies())();
  }, []);

  return [measurementFamilies, fetchMeasurementFamilies];
};

export {useMeasurementFamilies};
