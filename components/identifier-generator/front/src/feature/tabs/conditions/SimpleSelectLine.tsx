import React, {useCallback} from 'react';
import {FamilyOperators, Operator, SimpleSelectCondition} from '../../models';
import {Button, Helper, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {OperatorSelector} from '../../components';
import {ChannelCode, getLabel, LocaleCode, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {SimpleSelectOptionsSelector} from '../../components/SimpleSelectOptionsSelector';
import {OptionCode} from '../../models/option';
import {ScopeAndLocaleSelector} from '../../components/ScopeAndLocaleSelector';
import {useGetAttributeByCode} from '../../hooks/useGetAttributeByCode';
import {Unauthorized} from '../../errors';

type SimpleSelectLineProps = {
  condition: SimpleSelectCondition;
  onChange: (condition: SimpleSelectCondition) => void;
  onDelete: () => void;
};

const SimpleSelectLine: React.FC<SimpleSelectLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();
  const locale = useUserContext().get('catalogLocale');
  const {data, isLoading, error} = useGetAttributeByCode(condition.attributeCode);

  const handleOperatorChange = useCallback(
    (operator: Operator) => {
      const {value, ...conditionWithoutValue} = condition;

      if ([Operator.IN, Operator.NOT_IN].includes(operator)) {
        onChange({...conditionWithoutValue, operator, value: value ?? []});
      } else {
        onChange({...conditionWithoutValue, operator});
      }
    },
    [condition, onChange]
  );

  const handleSelectCodesChange = (optionCodes: OptionCode[]) => {
    onChange({...condition, value: optionCodes});
  };

  const handleScopeAndLocaleChange = (newValue: {scope?: ChannelCode | null; locale?: LocaleCode | null}) => {
    onChange({...condition, ...newValue});
  };

  return (
    <Table.Row aria-colspan={6}>
      {error ? (
        <Table.Cell colSpan={6}>
          <Helper level="error">
            {translate(error instanceof Unauthorized ? 'pim_error.unauthorized_list_attributes' : 'pim_error.general')}
          </Helper>
        </Table.Cell>
      ) : isLoading ? (
        <Table.Cell colSpan={6}>
          <Styled.ConditionLineSkeleton aria-colspan={6}>This is loading</Styled.ConditionLineSkeleton>
        </Table.Cell>
      ) : (
        <>
          <Styled.TitleCell colSpan={1}>
            {getLabel(data?.labels || {}, locale, condition.attributeCode)}
          </Styled.TitleCell>
          <Styled.CellInputContainer colSpan={1}>
            <Styled.InputContainer>
              <OperatorSelector
                operator={condition.operator}
                onChange={handleOperatorChange}
                operators={FamilyOperators}
              />
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
          <ScopeAndLocaleSelector
            locale={condition.locale}
            scope={condition.scope}
            attributeCode={condition.attributeCode}
            onChange={handleScopeAndLocaleChange}
          />
          <Table.ActionCell colSpan={1}>
            <Button onClick={onDelete} ghost level="danger">
              {translate('pim_common.delete')}
            </Button>
          </Table.ActionCell>
        </>
      )}
    </Table.Row>
  );
};

export {SimpleSelectLine};
