import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isMultiSelectSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const MultiSelectConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isMultiSelectSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for multi select configurator`);
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <CodeLabelCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Collapse>
  );
};

export {MultiSelectConfigurator};
