import {ColumnProperties} from '../TableStructureApp';
import {Field, Helper, NumberInput} from 'akeneo-design-system';
import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnValidation, TextColumnDefinition} from '../../models';

const TextProperties: ColumnProperties = ({selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const textSelectedColumn = selectedColumn as TextColumnDefinition;

  const isMaxLengthInvalid =
    typeof textSelectedColumn.validations.max_length !== 'undefined' &&
    (textSelectedColumn.validations.max_length < 1 || textSelectedColumn.validations.max_length > 100);

  const handleValidationChange = (validation: ColumnValidation) => {
    textSelectedColumn.validations = {...textSelectedColumn.validations, ...validation};
    handleChange(textSelectedColumn);
  };

  return (
    <Field label={translate('pim_table_attribute.validations.max_length')}>
      <NumberInput
        invalid={isMaxLengthInvalid}
        value={`${textSelectedColumn.validations.max_length}`}
        onChange={value => handleValidationChange({max_length: value === '' ? undefined : parseInt(value)})}
        min={1}
        max={100}
        step={1}
      />
      {isMaxLengthInvalid && (
        <Helper level='error'>{translate('pim_table_attribute.validations.max_length_range', {maximum: 100})}</Helper>
      )}
    </Field>
  );
};

export default TextProperties;
