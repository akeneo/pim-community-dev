import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Source} from '../../../models';
import {PropertySelector} from './PropertySelector';

type PropertyOperationsProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const PropertyOperations = ({source, validationErrors, onSourceChange}: PropertyOperationsProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  const selector = (
    <PropertySelector
      propertyName={source.code}
      selection={source.selection}
      validationErrors={filterErrors(validationErrors, '[selection]')}
      onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
    />
  );

  return (
    selector && (
      <Collapse
        collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
        label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
        isOpen={isSelectorCollapsed}
        onCollapse={toggleSelectorCollapse}
      >
        {selector}
      </Collapse>
    )
  );
};

export {PropertyOperations};
