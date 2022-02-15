import React from 'react';
import {MeasurementFamilyCode, MeasurementUnit} from '../models/MeasurementFamily';
import {useMeasurementFamilies} from './useMeasurementFamilies';

const useMeasurementUnits: (measurementFamilyCode?: MeasurementFamilyCode) => MeasurementUnit[] | undefined =
  measurementFamilyCode => {
    const measurementFamilies = useMeasurementFamilies();

    return React.useMemo(() => {
      return measurementFamilies?.find(({code}) => code === measurementFamilyCode)?.units;
    }, [measurementFamilies, measurementFamilyCode]);
  };

export {useMeasurementUnits};
