import {Field} from 'akeneo-design-system';
import React from 'react';
import {castMeasurementColumnDefinition} from '../../models';
import {ColumnProperties} from './index';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MeasurementFamilySelector} from '../MeasurementFamilySelector';
import {MeasurementUnitSelector} from '../MeasurementUnitSelector';
import {MeasurementUnitCode} from '../../models/MeasurementFamily';

const MeasurementProperties: ColumnProperties = ({selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const column = castMeasurementColumnDefinition(selectedColumn);

  const onChange = (measurementUnitCode: MeasurementUnitCode) => {
    handleChange({
      ...column,
      measurement_default_unit_code: measurementUnitCode,
    });
  };

  return (
    <>
      <Field
        label={translate('pim_table_attribute.form.attribute.measurement_family')}
        requiredLabel={translate('pim_common.required_label')}>
        <MeasurementFamilySelector readOnly value={column.measurement_family_code} />
      </Field>
      <Field
        label={translate('pim_table_attribute.form.attribute.measurement_default_unit')}
        requiredLabel={translate('pim_common.required_label')}>
        <MeasurementUnitSelector
          onChange={onChange}
          measurementFamilyCode={column.measurement_family_code}
          value={column.measurement_default_unit_code}
        />
      </Field>
    </>
  );
};

export default MeasurementProperties;
