import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {NoOperationsPlaceholder} from './NoOperationsPlaceholder';
import {AttributeSelector} from './AttributeSelector/AttributeSelector';

type AttributeOperationsProps = {
  attribute: Attribute;
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const AttributeOperations = ({attribute, source, validationErrors, onSourceChange}: AttributeOperationsProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  switch (attribute.type) {
    case 'pim_catalog_text':
    case 'pim_catalog_textarea':
    case 'pim_catalog_identifier':
    case 'pim_catalog_boolean':
    case 'pim_catalog_number':
      return <NoOperationsPlaceholder />;
  }

  const selector = (
    <AttributeSelector
      attribute={attribute}
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

export {AttributeOperations};
