import React from 'react';
import {Checkbox} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {isNumberTarget, NumberSourceParameter} from './model';
import {AttributeDataMappingConfiguratorProps} from '../../../../models';
import {InvalidAttributeTargetError} from '../error/InvalidAttributeTargetError';
import {AttributeTargetParameters, Operations, Sources} from '../../../../components';
import {DecimalSeparatorField} from '../../common/DecimalSeparatorField';

const NumberConfigurator = ({
  dataMapping,
  attribute,
  columns,
  onRefreshSampleData,
  onSourcesChange,
  onTargetChange,
  validationErrors,
}: AttributeDataMappingConfiguratorProps) => {
  const target = dataMapping.target;
  if (!isNumberTarget(target)) {
    throw new InvalidAttributeTargetError(`Invalid target data "${target.code}" for number configurator`);
  }

  const translate = useTranslate();
  const decimalSeparatorErrors = filterErrors(validationErrors, '[target][decimal_separator]');

  const handleSourceParameterChange = (updatedSourceParameter: NumberSourceParameter) =>
    onTargetChange({...target, source_parameter: updatedSourceParameter});

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, action_if_empty: clearIfEmpty ? 'clear' : 'skip'});

  return (
    <>
      <AttributeTargetParameters
        attribute={attribute}
        target={dataMapping.target}
        validationErrors={filterErrors(validationErrors, '[target]')}
        onTargetChange={onTargetChange}
      >
        <Checkbox checked={'clear' === target.action_if_empty} onChange={handleClearIfEmptyChange}>
          {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
        </Checkbox>
        {attribute.decimals_allowed && (
          <DecimalSeparatorField
            value={target.source_parameter.decimal_separator}
            onChange={decimalSeparator =>
              handleSourceParameterChange({...target.source_parameter, decimal_separator: decimalSeparator})
            }
            validationErrors={decimalSeparatorErrors}
          />
        )}
      </AttributeTargetParameters>
      <Sources
        sources={dataMapping.sources}
        columns={columns}
        validationErrors={filterErrors(validationErrors, '[sources]')}
        onSourcesChange={onSourcesChange}
      />
      <Operations dataMapping={dataMapping} onRefreshSampleData={onRefreshSampleData} />
    </>
  );
};

export {NumberConfigurator};
