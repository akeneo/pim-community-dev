import {Field, Helper, NumberInput} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnValidation, TextColumnDefinition} from '../../models';
import {ColumnProperties} from './index';
import {TABLE_STRING_MAX_LENGTH} from '../../product/CellInputs/TextInput';

const TextProperties: ColumnProperties = ({selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const textSelectedColumn = selectedColumn as TextColumnDefinition;

  const isMaxLengthInvalid =
    typeof textSelectedColumn.validations.max_length !== 'undefined' &&
    (textSelectedColumn.validations.max_length < 1 ||
      textSelectedColumn.validations.max_length > TABLE_STRING_MAX_LENGTH);

  const handleValidationChange = (validation: ColumnValidation) => {
    textSelectedColumn.validations = {...textSelectedColumn.validations, ...validation};
    handleChange(textSelectedColumn);
  };

  return (
    <Field label={translate('pim_table_attribute.validations.max_length')}>
      <NumberInput
        invalid={isMaxLengthInvalid}
        value={
          typeof textSelectedColumn.validations.max_length === 'undefined'
            ? ''
            : `${textSelectedColumn.validations.max_length}`
        }
        onChange={value => handleValidationChange({max_length: value === '' ? undefined : parseInt(value)})}
        min={1}
        max={TABLE_STRING_MAX_LENGTH}
        step={1}
      />
      {isMaxLengthInvalid && (
        <Helper level='error'>
          {translate('pim_table_attribute.validations.max_length_range', {maximum: TABLE_STRING_MAX_LENGTH})}
        </Helper>
      )}
    </Field>
  );
};

export default TextProperties;
