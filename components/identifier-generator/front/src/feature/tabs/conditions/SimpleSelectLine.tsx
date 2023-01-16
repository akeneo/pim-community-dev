import React, {useCallback, useMemo} from 'react';
import {Operator, SimpleSelectCondition, SimpleSelectOperators} from '../../models';
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
  const label = useMemo(
    () => (isLoading || error ? undefined : getLabel(data?.labels || {}, locale, condition.attributeCode)),
    [condition.attributeCode, data, error, isLoading, locale]
  );

  const handleOperatorChange = useCallback(
    (operator: Operator) => {
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
    <>
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
          <Styled.TitleCell colSpan={1}>{label}</Styled.TitleCell>
          <Styled.CellInputContainer colSpan={1}>
            <Styled.InputContainer>
              <OperatorSelector
                operator={condition.operator}
                onChange={handleOperatorChange}
                operators={SimpleSelectOperators}
              />
            </Styled.InputContainer>
          </Styled.CellInputContainer>

          {(condition.operator === Operator.IN || condition.operator === Operator.NOT_IN) && (
            <Table.Cell>
              <SimpleSelectOptionsSelector
                attributeCode={condition.attributeCode}
                optionCodes={condition.value || []}
                onChange={handleSelectCodesChange}
              />
            </Table.Cell>
          )}

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
    </>
  );
};

export {SimpleSelectLine};
