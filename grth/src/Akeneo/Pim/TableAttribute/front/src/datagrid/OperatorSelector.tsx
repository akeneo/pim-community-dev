import React from 'react';
import {SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ColumnCode, FilterOperator, FilterValue} from '../models';
import {ValuesFilterMapping} from './FilterValues';
import {useAttributeContext} from '../contexts';

type OperatorSelectorProps = {
  columnCode?: ColumnCode;
  value?: FilterValue;
  onChange: (operator?: FilterOperator) => void;
};

const OperatorSelector: React.FC<OperatorSelectorProps> = ({columnCode, value, onChange}) => {
  const translate = useTranslate();
  const {attribute} = useAttributeContext();
  const dataType = attribute?.table_configuration?.find(({code}) => code === columnCode)?.data_type;
  const operators = Object.keys(ValuesFilterMapping[dataType || ''] || {}) as FilterOperator[];

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
