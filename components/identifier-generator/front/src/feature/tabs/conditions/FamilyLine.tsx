import React from 'react';
import {FamilyCode, FamilyCondition, FamilyOperators, Operator} from '../../models';
import {SelectInput, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FamiliesSelector} from '../../components';

type FamilyLineProps = {
  condition: FamilyCondition & {id: string};
  onChange: (condition: FamilyCondition & {id: string}) => void;
};

const FamilyLine: React.FC<FamilyLineProps> = ({condition, onChange}) => {
  const translate = useTranslate();

  const handleOperatorChange = (operator: Operator) => {
    switch (operator) {
      case Operator.IN:
      case Operator.NOT_IN:
        onChange({...condition, operator, value: condition.value ?? []});
        break;
      case Operator.EMPTY:
      case Operator.NOT_EMPTY:
        onChange({...condition, operator, value: undefined});
    }
  }

  const handleFamilyCodesChange = (familyCodes: FamilyCode[]) => {
    if (condition.operator === Operator.IN) {
      onChange({...condition, value: familyCodes});
    }
  }

  return (
    <Table.Row>
      <Styled.TitleCell>{translate('pim_common.status')}</Styled.TitleCell>
      <Styled.CellInputContainer>
        <Styled.InputContainer>
          <SelectInput
            value={condition.operator}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            onChange={(operator: string) => handleOperatorChange(operator as Operator)}
            clearable={false}
          >
            {FamilyOperators.map(operator => (
              <SelectInput.Option key={operator} value={operator}>{operator}</SelectInput.Option>
            ))}
          </SelectInput>
        </Styled.InputContainer>
      </Styled.CellInputContainer>
      <Table.Cell>
        {(condition.operator === Operator.IN || condition.operator === Operator.NOT_IN) && (
          <FamiliesSelector familyCodes={condition.value} onChange={handleFamilyCodesChange}/>
        )}
      </Table.Cell>
    </Table.Row>
  );
};

export {FamilyLine};
