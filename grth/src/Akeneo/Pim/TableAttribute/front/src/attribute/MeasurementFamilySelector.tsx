import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {MeasurementFamilyCode} from '../models/MeasurementFamily';
import {useMeasurementFamilies} from './useMeasurementFamilies';

type MeasurementFamilySelectorProps = {
  value?: MeasurementFamilyCode;
  onChange?: (value?: MeasurementFamilyCode) => void;
  readOnly?: boolean;
};

const MeasurementFamilySelector: React.FC<MeasurementFamilySelectorProps> = ({value, onChange, readOnly = false}) => {
  const userContext = useUserContext();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const measurementFamilies = useMeasurementFamilies();

  return (
    <SelectInput
      value={value || null}
      onChange={(value: MeasurementFamilyCode | null) => onChange?.(value || undefined)}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      readOnly={readOnly}
      clearLabel={translate('pim_common.clear_value')}
    >
      {(measurementFamilies || []).map(measurementFamily => (
        <SelectInput.Option
          title={getLabel(measurementFamily.labels, catalogLocale, measurementFamily.code)}
          value={measurementFamily.code}
          key={measurementFamily.code}
        >
          {getLabel(measurementFamily.labels, catalogLocale, measurementFamily.code)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {MeasurementFamilySelector};
