import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {Collapse} from 'akeneo-design-system';
import {isNumberSource} from './model';
import React, {useState} from 'react';
import {AttributeConfiguratorProps} from '../../../models';
import {NumberSelector} from './NumberSelector';
import {InvalidAttributeSourceError} from '../error';

const NumberConfigurator = ({source, onSourceChange, validationErrors}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isNumberSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for number configurator`);
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <NumberSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedNumberSelection => onSourceChange({...source, selection: updatedNumberSelection})}
      />
    </Collapse>
  );
};

export {NumberConfigurator};
