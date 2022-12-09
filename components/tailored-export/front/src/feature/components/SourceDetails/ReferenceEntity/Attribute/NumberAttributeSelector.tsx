import React from 'react';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeSelectorProps} from './AttributeSelector';
import {isReferenceEntityNumberAttributeSelection, ReferenceEntityAttributeSelection} from '../model';
import {availableDecimalSeparators, isNumberDecimalSeparator} from '../../Number/model';
import {isReferenceEntityCollectionNumberAttributeSelection} from '../../ReferenceEntityCollection/model';

const NumberAttributeSelector = <SelectionType extends ReferenceEntityAttributeSelection>({
  selection,
  onSelectionChange,
  validationErrors,
}: AttributeSelectorProps<SelectionType>) => {
  const translate = useTranslate();

  if (
    !isReferenceEntityNumberAttributeSelection(selection) &&
    !isReferenceEntityCollectionNumberAttributeSelection(selection)
  ) {
    throw new Error('Invalid selection type for Number Attribute Selector');
  }

  return (
    <Field label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}>
      <SelectInput
        invalid={0 < validationErrors.length}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}
        value={selection.decimal_separator}
        onChange={decimal_separator => {
          if (isNumberDecimalSeparator(decimal_separator)) {
            onSelectionChange({...selection, decimal_separator});
          }
        }}
      >
        {Object.entries(availableDecimalSeparators).map(([separator, name]) => (
          <SelectInput.Option
            key={separator}
            title={translate(`akeneo.tailored_export.column_details.sources.selection.decimal_separator.${name}`)}
            value={separator}
          >
            {translate(`akeneo.tailored_export.column_details.sources.selection.decimal_separator.${name}`)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Field>
  );
};

export {NumberAttributeSelector};
