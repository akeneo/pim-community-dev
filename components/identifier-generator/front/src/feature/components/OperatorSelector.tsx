import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from './Styled';
import {useIdentifierGeneratorAclContext} from '../context';

type OperatorSelectorProps = {
  operator: Operator | null;
  onChange: (operator: Operator) => void;
  operators: Operator[];
  invalid?: boolean;
  placeholder?: string;
  isInSelection?: boolean;
};

const OperatorSelector: FC<OperatorSelectorProps> = ({
  operator,
  onChange,
  operators,
  isInSelection = false,
  ...rest
}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  return (
    <Styled.OperatorSelectCondition
      {...rest}
      value={operator}
      placeholder={translate('pim_identifier_generator.structure.settings.operator.placeholder')}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.open')}
      onChange={(operator: string) => onChange(operator as Operator)}
      clearable={false}
      isInSelection={isInSelection}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
    >
      {operators.map(operator => (
        <SelectInput.Option key={operator} value={operator}>
          {translate(`pim_common.operators.${operator}`)}
        </SelectInput.Option>
      ))}
    </Styled.OperatorSelectCondition>
  );
};

export {OperatorSelector};
