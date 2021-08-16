import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {PriceCollectionSelector} from './PriceCollectionSelector';
import {isPriceCollectionSource} from './model';
import {InvalidAttributeSourceError} from '../error';

const PriceCollectionConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isPriceCollectionSource(source)) {
    throw new InvalidAttributeSourceError(`Invalid source data "${source.code}" for price collection configurator`);
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <PriceCollectionSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedPriceCollectionSelection =>
          onSourceChange({...source, selection: updatedPriceCollectionSelection})
        }
      />
    </Collapse>
  );
};

export {PriceCollectionConfigurator};
