import React, {useState} from "react";
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AssociationConfiguratorProps} from '../../../models';
import {isSimpleAssociationSource} from './model';
import {SimpleAssociationTypeSelector} from "./SimpleAssociationTypeSelector";

const SimpleAssociationTypeConfigurator = ({source, validationErrors, onSourceChange}: AssociationConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isSimpleAssociationSource(source)) {
    console.error(`Invalid source data "${source.code}" for association configurator`);

    return null;
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <SimpleAssociationTypeSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Collapse>
  );
};

export {SimpleAssociationTypeConfigurator};
