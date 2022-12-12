import React, {useState} from 'react';
import {Collapse, Pill} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {NumberSelection, isDefaultNumberSelection} from './model';
import {DecimalSeparatorDropdown} from '../../../components';

type NumberSelectorProps = {
  selection: NumberSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: NumberSelection) => void;
};

const NumberSelector = ({selection, validationErrors, onSelectionChange}: NumberSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[decimal_separator]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultNumberSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <DecimalSeparatorDropdown
        label={translate('akeneo.tailored_export.column_details.sources.selection.decimal_separator.title')}
        value={selection.decimal_separator}
        validationErrors={decimalSeparatorErrors}
        onChange={updatedValue => onSelectionChange({...selection, decimal_separator: updatedValue})}
      />
    </Collapse>
  );
};

export {NumberSelector};
