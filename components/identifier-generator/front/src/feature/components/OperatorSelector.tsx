import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';

type OperatorSelectorProps = {
  operator: Operator;
  onChange: (operator: Operator) => void;
  operators: Operator[];
  readonly: boolean;
};

const OperatorSelector: FC<OperatorSelectorProps> = ({operator, onChange, operators, readonly}) => {
  const translate = useTranslate();

  return (
    <SelectInput
      value={operator}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      onChange={(operator: string) => onChange(operator as Operator)}
      clearable={false}
      readOnly={readonly}
    >
      {operators.map(operator => (
        <SelectInput.Option key={operator} value={operator}>
          {translate(`pim_common.operators.${operator}`)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {OperatorSelector};
