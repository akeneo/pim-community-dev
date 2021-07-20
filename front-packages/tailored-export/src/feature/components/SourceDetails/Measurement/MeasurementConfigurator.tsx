import React, {useState} from 'react';
import {Collapse} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeConfiguratorProps} from '../../../models';
import {isMeasurementSource} from './model';
import {MeasurementSelector} from './MeasurementSelector';

const MeasurementConfigurator = ({source, validationErrors, onSourceChange}: AttributeConfiguratorProps) => {
  const translate = useTranslate();
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);

  if (!isMeasurementSource(source)) {
    console.error(`Invalid source data "${source.code}" for measurement configurator`);

    return null;
  }

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <MeasurementSelector
        selection={source.selection}
        validationErrors={filterErrors(validationErrors, '[selection]')}
        onSelectionChange={updatedMeasurementSelection =>
          onSourceChange({...source, selection: updatedMeasurementSelection})
        }
      />
    </Collapse>
  );
};

export {MeasurementConfigurator};
