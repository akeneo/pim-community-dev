import {Router} from '@akeneo-pim-community/shared';
import {MeasurementFamily} from '../models/MeasurementFamily';

const fetchAll = async (router: Router): Promise<MeasurementFamily[]> => {
  const url = router.generate('pim_enrich_measures_rest_index');
  const response = await fetch(url);

  return await response.json();
};

const MeasurementFamilyFetcher = {
  fetchAll,
};

export {MeasurementFamilyFetcher};
