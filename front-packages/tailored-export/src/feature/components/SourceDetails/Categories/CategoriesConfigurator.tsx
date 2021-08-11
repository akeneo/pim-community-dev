import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {PropertyConfiguratorProps} from '../../../models';
import {CodeLabelCollectionSelector} from '../common/CodeLabelCollectionSelector';
import {isCategoriesSource} from './model';
import {InvalidPropertySourceError} from '../error';

const CategoriesConfigurator = ({source, validationErrors, onSourceChange}: PropertyConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isCategoriesSource(source)) {
    throw new InvalidPropertySourceError(`Invalid source data "${source.code}" for categories configurator`);
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

export {CategoriesConfigurator};
