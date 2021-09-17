import {useIsMounted} from '@akeneo-pim-community/shared';
import {useFetchers} from '../contexts';
import {useState, useEffect} from 'react';
import {MeasurementFamily} from '../models';

const useMeasurementFamily = (measurementFamilyCode: string): MeasurementFamily | null => {
  const measurementFamilyFetcher = useFetchers().measurementFamily;
  const [measurementFamily, setMeasurementFamily] = useState<MeasurementFamily | null>(null);
  const isMounted = useIsMounted();

  useEffect(() => {
    measurementFamilyFetcher
      .fetchByCode(measurementFamilyCode)
      .then((measurementFamily: MeasurementFamily | undefined) => {
        if (!isMounted()) return;

        setMeasurementFamily(measurementFamily ?? null);
      });
  }, [measurementFamilyFetcher, measurementFamilyCode, isMounted]);

  return measurementFamily;
};

export {useMeasurementFamily};
