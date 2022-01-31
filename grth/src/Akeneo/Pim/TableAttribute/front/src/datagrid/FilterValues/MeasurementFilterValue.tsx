import React from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MeasurementValue} from '../../models/MeasurementFamily';
import {MeasurementFilterInput} from './MeasurementFilterInput';
import {useAttributeContext} from '../../contexts';
import {castMeasurementColumnDefinition} from '../../models';

const MeasurementFilterValue: TableFilterValueRenderer = ({value, onChange, columnCode}) => {
  const {attribute} = useAttributeContext();
  const column = attribute?.table_configuration?.find(({code}) => code === columnCode);

  return (
    <>
      {column && (
        <MeasurementFilterInput
          value={{
            amount: (value as MeasurementValue)?.amount || '',
            unit:
              (value as MeasurementValue)?.unit ||
              castMeasurementColumnDefinition(column).measurement_default_unit_code,
          }}
          onChange={onChange}
          measurementFamilyCode={castMeasurementColumnDefinition(column).measurement_family_code}
        />
      )}
    </>
  );
};

const useValueRenderer: FilteredValueRenderer = () => {
  return 'TODO';
};

export {useValueRenderer};
export default MeasurementFilterValue;
