import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeSelectorProps} from './AttributeSelector';
import {isReferenceEntityNumberAttributeSelection, ReferenceEntityAttributeSelection} from '../model';
import {isReferenceEntityCollectionNumberAttributeSelection} from '../../ReferenceEntityCollection/model';
import {DecimalSeparatorDropdown} from '../../../../components';

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
    <DecimalSeparatorDropdown
      label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
      value={selection.decimal_separator}
      validationErrors={validationErrors}
      onChange={updatedValue => onSelectionChange({...selection, decimal_separator: updatedValue})}
    />
  );
};

export {NumberAttributeSelector};
