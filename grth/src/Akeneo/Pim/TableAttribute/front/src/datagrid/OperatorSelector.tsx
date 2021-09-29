import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {FilterValuesMapping} from './FilterValues';
import {useTranslate} from '@akeneo-pim-community/shared';

type OperatorSelectorProps = {
  dataType?: string;
  value?: string;
  onChange: (operator: string | undefined) => void;
  filterValuesMapping: FilterValuesMapping;
};

const OperatorSelector: React.FC<OperatorSelectorProps> = ({dataType, value, onChange, filterValuesMapping}) => {
  const translate = useTranslate();
  const operators = Object.keys(filterValuesMapping[dataType || ''] || {});

  const handleChange = (value: string | null) => {
    onChange(null === value ? undefined : value);
  };

  return (
    <SelectInput
      clearLabel={translate('pim_common.clear_value')}
      clearable
      emptyResultLabel={translate('pim_common.no_result')}
      onChange={handleChange}
      placeholder='TODO Fill Operator'
      value={value || null}
      openLabel={translate('pim_common.open')}
      readOnly={typeof dataType === 'undefined'}>
      {(operators || []).map(operator => {
        return (
          <SelectInput.Option title={translate(`pim_common.operators.${operator}`)} value={operator} key={operator}>
            {translate(`pim_common.operators.${operator}`)}
          </SelectInput.Option>
        );
      })}
    </SelectInput>
  );
};

export {OperatorSelector};
