import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AssociationTypeConfiguratorProps} from '../../../models';
import {isQuantifiedAssociationTypeSource} from './model';
import {QuantifiedAssociationTypeSelector} from './QuantifiedAssociationTypeSelector';
import {InvalidAssociationTypeSourceError} from '../error';

const QuantifiedAssociationTypeConfigurator = ({
  source,
  validationErrors,
  onSourceChange,
}: AssociationTypeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isQuantifiedAssociationTypeSource(source)) {
    throw new InvalidAssociationTypeSourceError(
      `Invalid source data "${source.code}" for quantified association configurator`
    );
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <QuantifiedAssociationTypeSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedSelection => onSourceChange({...source, selection: updatedSelection})}
      />
    </Collapse>
  );
};

export {QuantifiedAssociationTypeConfigurator};
