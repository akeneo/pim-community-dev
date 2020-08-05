import { Router } from '../dependenciesTools';
import { httpGet } from './fetch';
import { MeasurementFamily } from '../models';

const fetchAllMeasurementFamilies = async (
  router: Router
): Promise<MeasurementFamily[]> => {
  const response = await httpGet(
    router.generate('pim_enrich_measures_rest_index')
  );
  return await response.json();
};

export { fetchAllMeasurementFamilies };
