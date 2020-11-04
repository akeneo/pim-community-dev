import {fetchAllMeasurementFamilies} from '../fetch/MeasurementFamilyFetcher';
import {Router} from '../dependenciesTools';
import {MeasurementFamily, MeasurementFamilyCode} from '../models';

type IndexedMeasurementFamilies = {[code: string]: MeasurementFamily};
let cachedMeasurementFamilies: IndexedMeasurementFamilies | null;

const clearMeasurementFamilyRepositoryCache = () => {
  cachedMeasurementFamilies = null;
};

const getAllMeasurementFamilies: (
  router: Router
) => Promise<IndexedMeasurementFamilies> = async router => {
  if (!cachedMeasurementFamilies) {
    const measurementFamilies = await fetchAllMeasurementFamilies(router);
    cachedMeasurementFamilies = measurementFamilies.reduce(
      (previousValue, measurementFamily: MeasurementFamily) => ({
        ...previousValue,
        [measurementFamily.code]: measurementFamily,
      }),
      {}
    );
  }
  return cachedMeasurementFamilies;
};

const getMeasurementFamilyByCode: (
  code: MeasurementFamilyCode,
  router: Router
) => Promise<MeasurementFamily | null> = async (measurementCode, router) => {
  const measurementFamilies = await getAllMeasurementFamilies(router);
  return measurementFamilies[measurementCode] || null;
};

export {
  getAllMeasurementFamilies,
  IndexedMeasurementFamilies,
  getMeasurementFamilyByCode,
  clearMeasurementFamilyRepositoryCache,
};
