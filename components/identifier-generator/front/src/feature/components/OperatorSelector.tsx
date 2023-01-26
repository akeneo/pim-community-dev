import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierGeneratorAclContext} from '../context';

type OperatorSelectorProps = {
  operator: Operator;
  onChange: (operator: Operator) => void;
  operators: Operator[];
};

const OperatorSelector: FC<OperatorSelectorProps> = ({operator, onChange, operators}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  return (
    <SelectInput
      value={operator}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      onChange={(operator: string) => onChange(operator as Operator)}
      clearable={false}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
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
