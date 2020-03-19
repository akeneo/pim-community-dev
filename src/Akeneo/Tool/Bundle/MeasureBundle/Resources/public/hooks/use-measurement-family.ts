import {useCallback, useContext, useEffect, useState} from 'react';
import {MeasurementFamily, MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';
import {RouterContext} from 'akeneomeasure/context/router-context';
import {baseFetcher} from 'akeneomeasure/shared/fetcher/base-fetcher';

const useMeasurementFamily = (
  measurementFamilyCode: MeasurementFamilyCode
): [MeasurementFamily | null, (measurementFamily: MeasurementFamily) => void] => {
  const [measurementFamily, setMeasurementFamily] = useState<MeasurementFamily | null>(null);
  const route = useContext(RouterContext).generate('pim_enrich_measures_rest_index');

  const fetchMeasurementFamily = useCallback(
    async (measurementFamilyCode: MeasurementFamilyCode) => {
      const measurementFamilies = await baseFetcher(route);
      const measurementFamily = measurementFamilies.find(
        (measurementFamily: MeasurementFamily) => measurementFamily.code === measurementFamilyCode
      );
      setMeasurementFamily(measurementFamily);
    },
    [route, setMeasurementFamily]
  );

  useEffect(() => {
    (async () => fetchMeasurementFamily(measurementFamilyCode))();
  }, []);

  return [measurementFamily, setMeasurementFamily];
};

export {useMeasurementFamily};
