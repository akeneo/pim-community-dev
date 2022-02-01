import React from 'react';
import {FilteredValueRenderer, TableFilterValueRenderer} from './index';
import {MeasurementValue} from '../../models/MeasurementFamily';
import {MeasurementFilterInput} from './MeasurementFilterInput';
import {useAttributeContext} from '../../contexts';
import {castMeasurementColumnDefinition} from '../../models';
import {useMeasurementUnits} from '../../attribute/useMeasurementUnits';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';

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

const useValueRenderer: FilteredValueRenderer = (value, columnCode) => {
  const catalogLocale = useUserContext().get('catalogLocale');
  const {attribute} = useAttributeContext();
  const column = attribute?.table_configuration?.find(({code}) => code === columnCode);
  const measurementFamilyCode =
    column && Object.prototype.hasOwnProperty.call(column || {}, 'measurement_family_code')
      ? castMeasurementColumnDefinition(column).measurement_family_code
      : undefined;
  const units = useMeasurementUnits(measurementFamilyCode);

  const {amount, unit} = (value as MeasurementValue | undefined) || {};

  const unitEntity = units?.find(({code}) => code === unit);
  const translatedUnit = unitEntity?.symbol || getLabel(unitEntity?.labels || {}, catalogLocale, unit || '');

  return `${amount} ${translatedUnit}`;
};

export {useValueRenderer};
export default MeasurementFilterValue;
