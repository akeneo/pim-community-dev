import React, {useCallback} from 'react';
import {FamilyOperators, Operator, SimpleSelectCondition} from '../../models';
import {Button, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {OperatorSelector} from '../../components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SimpleSelectOptionsSelector} from '../../components/SimpleSelectOptionsSelector';
import {OptionCode} from '../../models/option';

type SimpleSelectLineProps = {
  condition: SimpleSelectCondition;
  onChange: (condition: SimpleSelectCondition) => void;
  onDelete: () => void;
};

const SimpleSelectLine: React.FC<SimpleSelectLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();

  const handleOperatorChange = useCallback((operator: Operator) => {
    const {value, ...conditionWithoutValue} = condition;

    if ([Operator.IN, Operator.NOT_IN].includes(operator)) {
      onChange({...conditionWithoutValue, operator, value: value ?? []});
    } else {
      onChange({...conditionWithoutValue, operator});
    }
  }, [condition, onChange]);

  const handleSelectCodesChange = (optionCodes: OptionCode[]) => {
    onChange({...condition, value: optionCodes});
  };

  return (
    <Table.Row aria-colspan={4}>
      <Styled.TitleCell colSpan={1}>Name of the attribute</Styled.TitleCell>
      <Styled.CellInputContainer colSpan={1}>
        <Styled.InputContainer>
          <OperatorSelector operator={condition.operator} onChange={handleOperatorChange} operators={FamilyOperators} />
        </Styled.InputContainer>
      </Styled.CellInputContainer>
      <Table.Cell colSpan={1}>
        {(condition.operator === Operator.IN || condition.operator === Operator.NOT_IN) && (
          <SimpleSelectOptionsSelector
            attributeCode={condition.attributeCode}
            optionCodes={condition.value || []}
            onChange={handleSelectCodesChange}
          />
        )}
      </Table.Cell>
      <Table.ActionCell colSpan={1}>
        <Button onClick={onDelete} ghost level="danger">
          {translate('pim_common.delete')}
        </Button>
      </Table.ActionCell>
    </Table.Row>
  );
};

export {SimpleSelectLine};
