import {Field, Helper, NumberInput} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnValidation, NumberColumnDefinition} from '../../models';
import {Checkbox} from '@akeneo-pim-community/connectivity-connection/src/common';
import {ColumnProperties} from './index';

const NumberProperties: ColumnProperties = ({selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const numberSelectedColumn = selectedColumn as NumberColumnDefinition;

  const handleValidationChange = (validation: ColumnValidation) => {
    numberSelectedColumn.validations = {...numberSelectedColumn.validations, ...validation};
    handleChange(numberSelectedColumn);
  };

  const isMinGreaterThanMax =
    'undefined' !== typeof numberSelectedColumn.validations.min &&
    'undefined' !== typeof numberSelectedColumn.validations.max &&
    numberSelectedColumn.validations.min > numberSelectedColumn.validations.max;

  return (
    <>
      <Field label={translate('pim_table_attribute.validations.min')}>
        <NumberInput
          value={
            typeof numberSelectedColumn.validations.min === 'undefined' ? '' : `${numberSelectedColumn.validations.min}`
          }
          onChange={value => handleValidationChange({min: value === '' ? undefined : parseFloat(value)})}
        />
      </Field>
      <Field label={translate('pim_table_attribute.validations.max')}>
        <NumberInput
          value={
            typeof numberSelectedColumn.validations.max === 'undefined' ? '' : `${numberSelectedColumn.validations.max}`
          }
          onChange={value => handleValidationChange({max: value === '' ? undefined : parseFloat(value)})}
        />
        {isMinGreaterThanMax && (
          <Helper level='error'>{translate('pim_table_attribute.validations.max_greater_than_min')}</Helper>
        )}
      </Field>
      <Checkbox
        checked={numberSelectedColumn.validations.decimals_allowed ?? false}
        onChange={event => handleValidationChange({decimals_allowed: event.target.checked})}
      >
        {translate('pim_table_attribute.validations.decimals_allowed')}
      </Checkbox>
    </>
  );
};

export default NumberProperties;
