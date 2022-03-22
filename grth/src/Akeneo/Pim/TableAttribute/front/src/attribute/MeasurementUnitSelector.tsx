import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {MeasurementFamilyCode, MeasurementUnitCode} from '../models/MeasurementFamily';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useMeasurementUnits} from './useMeasurementUnits';

type MeasurementUnitSelectorProps = {
  value?: MeasurementUnitCode;
  measurementFamilyCode?: MeasurementFamilyCode;
  onChange: (value: MeasurementUnitCode) => void;
};

const MeasurementUnitSelector: React.FC<MeasurementUnitSelectorProps> = ({value, measurementFamilyCode, onChange}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const measurementUnits = useMeasurementUnits(measurementFamilyCode);

  return (
    <SelectInput
      value={value || null}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      onChange={(value: MeasurementUnitCode) => onChange(value)}
      readOnly={typeof measurementFamilyCode === 'undefined'}
      clearable={false}
    >
      {(measurementUnits || []).map(({code, labels}) => (
        <SelectInput.Option title={getLabel(labels, catalogLocale, code)} value={code} key={code}>
          {getLabel(labels, catalogLocale, code)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {MeasurementUnitSelector};
