import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isFileSource} from './model';
import {FileSelector} from './FileSelector';
import {InvalidAttributeSourceError} from '../error';

const FileConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isFileSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for file configurator`);
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <FileSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedFileSelection => onSourceChange({...source, selection: updatedFileSelection})}
      />
    </Collapse>
  );
};

export {FileConfigurator};
