import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelSelector} from '../common/CodeLabelSelector';
import {isFamilySource} from './model';

const FamilyConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isFamilySource(source)) {
    console.error(`Invalid source data "${source.code}" for family configurator`);

    return null;
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <CodeLabelSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Collapse>
  );
};

export {FamilyConfigurator};
