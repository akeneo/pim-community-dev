import {useCallback, useContext, useEffect, useState} from 'react';
import {MeasurementFamily, MeasurementFamilyCode} from 'akeneomeasure/model/measurement-family';
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

const useMeasurementFamily = (
  measurementFamilyCode: MeasurementFamilyCode
): [MeasurementFamily | null, (measurementFamily: MeasurementFamily) => void] => {
  const [measurementFamily, setMeasurementFamily] = useState<MeasurementFamily | null>(null);
  const route = useContext(RouterContext).generate('pim_enrich_measures_rest_index');

  const fetchMeasurementFamily = useCallback(
    async (measurementFamilyCode: MeasurementFamilyCode) => {
      const measurementFamilies = await fetcher(route);
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

export {useMeasurementFamilies, useMeasurementFamily};
