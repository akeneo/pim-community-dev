import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useAttribute} from '../../../hooks';
import {Source} from '../../../models';
import {NoOperationsPlaceholder} from './NoOperationsPlaceholder';
import {Selector} from './Selector/Selector';

type OperationsProps = {
  source: Source;
  onSourceChange: (updatedSource: Source) => void;
};

const Operations = ({source, onSourceChange}: OperationsProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);
  const attribute = useAttribute(source.code);

  if (null === attribute) return null;

  switch (attribute.type) {
    case 'pim_catalog_text':
    case 'pim_catalog_textarea':
    case 'pim_catalog_identifier':
    case 'pim_catalog_boolean':
    case 'pim_catalog_number':
      return <NoOperationsPlaceholder />;
  }

  const selector = (
    <Selector
      attribute={attribute}
      selection={source.selection}
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

export {Operations};
