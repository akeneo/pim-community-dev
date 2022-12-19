import React, {FC} from 'react'
import {SelectInput} from 'akeneo-design-system';
import {FamilyOperators, Operator} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type OperatorSelectorProps = {
  operator: Operator;
  onChange: (operator: Operator) => void;
  operators: Operator[];
}

const OperatorSelector: FC<OperatorSelectorProps> = ({operator, onChange, operators}) => {
  const translate = useTranslate();

  return <SelectInput
    value={operator}
    emptyResultLabel={translate('pim_common.no_result')}
    openLabel={translate('pim_common.open')}
    onChange={(operator: string) => onChange(operator as Operator)}
    clearable={false}
  >
    {operators.map(operator => (
      <SelectInput.Option key={operator} value={operator}>
        {translate(`pim_common.operators.${operator}`)}
      </SelectInput.Option>
    ))}
  </SelectInput>
}

export {OperatorSelector};
