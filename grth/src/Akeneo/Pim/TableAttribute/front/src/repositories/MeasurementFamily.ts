import {Router} from '@akeneo-pim-community/shared';
import {MeasurementFamily} from '../models/MeasurementFamily';
import {MeasurementFamilyFetcher} from '../fetchers/MeasurementFamilyFetcher';

let measurementFamiliesCache: MeasurementFamily[] | undefined = undefined;

const all: (router: Router) => Promise<MeasurementFamily[]> = async router => {
  if (typeof measurementFamiliesCache === 'undefined') {
    measurementFamiliesCache = await MeasurementFamilyFetcher.fetchAll(router);
  }

  return Promise.resolve(measurementFamiliesCache);
};

const MeasurementFamilyRepository = {
  all,
};

export {MeasurementFamilyRepository};
