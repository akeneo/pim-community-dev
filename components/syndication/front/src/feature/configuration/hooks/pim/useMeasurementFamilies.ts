import {useIsMounted} from '@akeneo-pim-community/shared';
import {useFetchers} from '../../contexts';
import {useState, useEffect} from 'react';
import {MeasurementFamily} from '../../models';

const useMeasurementFamilies = (): MeasurementFamily[] | null => {
  const measurementFamilyFetcher = useFetchers().measurementFamily;
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    measurementFamilyFetcher.fetchAll().then((measurementFamilies: MeasurementFamily[]) => {
      if (!isMounted()) return;

      setMeasurementFamilies(measurementFamilies ?? null);
    });
  }, [measurementFamilyFetcher, isMounted]);

  return measurementFamilies;
};

export {useMeasurementFamilies};
