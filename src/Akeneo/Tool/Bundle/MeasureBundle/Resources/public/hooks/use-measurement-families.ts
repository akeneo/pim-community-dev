import {useCallback, useContext, useEffect, useState} from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {RouterContext} from 'akeneomeasure/context/router-context';

const fetcher = async (route: string) => {
  const response = await fetch(route);

  return await response.json();
};

const useMeasurementFamilies = (): [MeasurementFamily[] | null, () => Promise<void>] => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);
  const route = useContext(RouterContext).generate('pim_enrich_measures_rest_index');

  const fetchMeasurementFamilies = useCallback(async () => {
    setMeasurementFamilies(await fetcher(route));
  }, [route, setMeasurementFamilies]);

  useEffect(() => {
    (async () => fetchMeasurementFamilies())();
  }, []);

  return [measurementFamilies, fetchMeasurementFamilies];
};

export {useMeasurementFamilies};
