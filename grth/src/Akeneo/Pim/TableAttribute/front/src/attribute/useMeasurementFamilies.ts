import React from 'react';
import {MeasurementFamily} from '../models/MeasurementFamily';
import {MeasurementFamilyRepository} from '../repositories/MeasurementFamily';
import {useRouter} from '@akeneo-pim-community/shared';

const useMeasurementFamilies = (): MeasurementFamily[] | undefined => {
  const router = useRouter();
  const [measurementFamilies, setMeasurementFamilies] = React.useState<MeasurementFamily[] | undefined>();

  React.useEffect(() => {
    MeasurementFamilyRepository.all(router).then(measurementFamilies => setMeasurementFamilies(measurementFamilies));
  }, [router]);

  return measurementFamilies;
};

export {useMeasurementFamilies};
