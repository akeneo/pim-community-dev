import React, {useCallback, useMemo} from 'react';
import {Operator, SimpleOrMultiSelectCondition, SimpleSelectOperators} from '../../models';
import {Button, Helper, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {OperatorSelector, ScopeAndLocaleSelector} from '../../components';
import {
  ChannelCode,
  getLabel,
  LocaleCode,
  useSecurity,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {SimpleSelectOptionsSelector} from '../../components/SimpleSelectOptionsSelector';
import {OptionCode} from '../../models/option';
import {useGetAttributeByCode} from '../../hooks';
import {AttributeNotFound, Unauthorized} from '../../errors';
import {useIdentifierGeneratorAclContext} from '../../context';

type SimpleOrMultiSelectLineProps = {
  condition: SimpleOrMultiSelectCondition;
  onChange: (condition: SimpleOrMultiSelectCondition) => void;
  onDelete: () => void;
};

const SimpleOrMultiSelectLine: React.FC<SimpleOrMultiSelectLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const locale = useUserContext().get('catalogLocale');
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const {data, isLoading, error} = useGetAttributeByCode(condition.attributeCode);
  const canAccessAttributes = isGranted('pim_enrich_attribute_index');

  const label = useMemo(
    () =>
      isLoading || error
        ? `[${condition.attributeCode}]`
        : getLabel(data?.labels || {}, locale, condition.attributeCode),
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

  if (!canAccessAttributes) {
    return (
      <Table.Cell colSpan={3}>
        <Helper level="info">{translate('pim_error.unauthorized_list_properties')}</Helper>
      </Table.Cell>
    );
  }

  return (
    <>
      {isLoading ? (
        <Table.Cell colSpan={3}>
          <Styled.ConditionLineSkeleton aria-colspan={3}>This is loading</Styled.ConditionLineSkeleton>
        </Table.Cell>
      ) : (
        <>
          <Styled.TitleCell>{label}</Styled.TitleCell>
          <Styled.SelectionInputsContainer>
            {error ? (
              <Helper level="error">
                {translate(
                  error instanceof Unauthorized
                    ? 'pim_error.unauthorized_list_attributes'
                    : error instanceof AttributeNotFound
                    ? 'pim_error.selection_attribute_not_found'
                    : 'pim_error.general'
                )}
              </Helper>
            ) : (
              <>
                <OperatorSelector
                  operator={condition.operator}
                  onChange={handleOperatorChange}
                  operators={SimpleSelectOperators}
                  isInSelection={true}
                />

                {(condition.operator === Operator.IN || condition.operator === Operator.NOT_IN) && (
                  <SimpleSelectOptionsSelector
                    attributeCode={condition.attributeCode}
                    optionCodes={condition.value || []}
                    onChange={handleSelectCodesChange}
                  />
                )}

                <ScopeAndLocaleSelector
                  locale={condition.locale}
                  scope={condition.scope}
                  attributeCode={condition.attributeCode}
                  onChange={handleScopeAndLocaleChange}
                />
              </>
            )}
          </Styled.SelectionInputsContainer>
          <Table.ActionCell colSpan={1}>
            {identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted && (
              <Button onClick={onDelete} ghost level="danger">
                {translate('pim_common.delete')}
              </Button>
            )}
          </Table.ActionCell>
        </>
      )}
    </>
  );
};

export {SimpleOrMultiSelectLine};
