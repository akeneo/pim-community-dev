import {useState, useEffect, useContext} from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {RouterContext} from 'akeneomeasure/context/router-context';

const fetcher = async (route: string) => {
  const response = await fetch(route);

  return await response.json();
};

export const useMeasurementFamilies = () => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);
  const route = useContext(RouterContext).generate('pim_enrich_measures_rest_index');

  useEffect(() => {
    (async () => setMeasurementFamilies(await fetcher(route)))();
  }, []);

  return measurementFamilies;
};
