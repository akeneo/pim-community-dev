import React from 'react';
import {FamilyCode, FamilyCondition, FamilyOperators, Operator} from '../../models';
import {Button, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamiliesSelector, OperatorSelector} from '../../components';

type FamilyLineProps = {
  condition: FamilyCondition & {id: string};
  onChange: (condition: FamilyCondition & {id: string}) => void;
  onDelete: () => void;
};

const FamilyLine: React.FC<FamilyLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();

  const handleOperatorChange = (operator: Operator) => {
    const {value, ...conditionWithoutValue} = condition;
    switch (operator) {
      case Operator.IN:
      case Operator.NOT_IN:
        onChange({...conditionWithoutValue, operator, value: value ?? []});
        break;
      case Operator.EMPTY:
      case Operator.NOT_EMPTY:
        onChange({...conditionWithoutValue, operator});
    }
  };

  const handleFamilyCodesChange = (familyCodes: FamilyCode[]) => {
    onChange({...condition, value: familyCodes});
  };

  return (
    <Table.Row>
      <Styled.TitleCell>{translate('pim_common.family')}</Styled.TitleCell>
      <Styled.CellInputContainer>
        <Styled.InputContainer>
          <OperatorSelector operator={condition.operator} onChange={handleOperatorChange} operators={FamilyOperators} />
        </Styled.InputContainer>
      </Styled.CellInputContainer>
      <Table.Cell>
        {(condition.operator === Operator.IN || condition.operator === Operator.NOT_IN) && (
          <FamiliesSelector familyCodes={condition.value} onChange={handleFamilyCodesChange} />
        )}
      </Table.Cell>
      <Table.ActionCell>
        <Button onClick={onDelete} ghost level="danger">
          {translate('pim_common.delete')}
        </Button>
      </Table.ActionCell>
    </Table.Row>
  );
};

export {FamilyLine};
