import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {FilterValuesMapping} from './FilterValues';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DataType, FilterOperator, FilterValue} from '../models';

type OperatorSelectorProps = {
  dataType?: DataType;
  value?: FilterValue;
  onChange: (operator?: FilterOperator) => void;
  filterValuesMapping: FilterValuesMapping;
};

const OperatorSelector: React.FC<OperatorSelectorProps> = ({dataType, value, onChange, filterValuesMapping}) => {
  const translate = useTranslate();
  const operators = Object.keys(filterValuesMapping[dataType || ''] || {}) as FilterOperator[];

  const handleChange = (operator: string | null) => {
    onChange(null === operator ? undefined : (operator as FilterOperator));
  };

  return (
    <SelectInput
      clearLabel={translate('pim_common.clear_value')}
      clearable
      emptyResultLabel={translate('pim_common.no_result')}
      onChange={handleChange}
      placeholder={translate('pim_table_attribute.datagrid.select_your_operator')}
      value={(value as string) || null}
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
