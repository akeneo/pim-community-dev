import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import {
  Operation,
  Operator,
} from '../../../../models/actions/Calculate/Operation';
import {
  ConstantOperand,
  FieldOperand,
  Operand,
} from '../../../../models/actions/Calculate/Operand';
import { ActionLeftSide, ActionRightSide, ActionTitle } from './ActionLine';
import styled from 'styled-components';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputAction } from '../../hooks';
import { Controller, useFormContext } from 'react-hook-form';
import {
  Attribute,
  AttributeCode,
  AttributeType,
  Locale,
} from '../../../../models';
import {
  fetchAttribute,
  useGetAttributeAtMount,
} from './attribute/attribute.utils';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import { InlineHelper } from '../../../../components/HelpersInfos/InlineHelper';
import {
  getMeasurementUnitValidator,
  MeasurementUnitSelector,
} from '../../../../components/Selectors/MeasurementUnitSelector';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../components/Selectors/LocaleSelector';
import { ActionFormContainer } from './style';
import { InputNumber } from '../../../../components/Inputs';
import {
  CurrencySelector,
  getCurrencyValidator,
} from '../../../../components/Selectors/CurrencySelector';
import { Currency } from '../../../../models/Currency';
import { useActiveCurrencies } from '../../hooks/useActiveCurrencies';
import { IndexedCurrencies } from '../../../../repositories/CurrencyRepository';

const CalculateActionGrid = styled.div`
  margin-top: 18px;
  display: grid;
  grid-template-columns: 1fr 300px;
  grid-gap: 40px;
`;

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

const ErrorBlock = styled.div`
  margin-top: 5px;
`;

const targetAttributeTypes: AttributeType[] = [
  AttributeType.NUMBER,
  AttributeType.PRICE_COLLECTION,
  AttributeType.METRIC,
];

const OperandView: React.FC<{ operand: Operand }> = ({ operand }) => {
  if (Object.keys(operand).includes('field')) {
    const fieldOperand = operand as FieldOperand;

    return (
      <FallbackField
        field={fieldOperand.field}
        scope={fieldOperand.scope || null}
        locale={fieldOperand.locale || null}
      />
    );
  }

  return (
    <span className='AknRule-attribute'>
      {(operand as ConstantOperand).value}
    </span>
  );
};

const AddView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by adding&nbsp;
      <OperandView operand={operand} />
      {source && (
        <>
          &nbsp;to&nbsp;
          <OperandView operand={source} />
        </>
      )}
    </>
  );
};

const SubstractView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by subtracting&nbsp;
      <OperandView operand={operand} />
      {source && (
        <>
          &nbsp;from&nbsp;
          <OperandView operand={source} />
        </>
      )}
    </>
  );
};

const MultiplyView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by multiplying&nbsp;
      {source && (
        <>
          <OperandView operand={source} />
          &nbsp;
        </>
      )}
      by&nbsp;
      <OperandView operand={operand} />
    </>
  );
};

const DivideView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by dividing&nbsp;
      {source && (
        <>
          <OperandView operand={source} />
          &nbsp;
        </>
      )}
      by&nbsp;
      <OperandView operand={operand} />
    </>
  );
};

const CalculateActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();
  const { setValue, watch } = useFormContext();
  const {
    formName,
    typeFormName,
    getFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<string | null>(lineNumber);
  const currencies = useActiveCurrencies();
  watch(formName('source'));
  watch(formName('operation_list'));
  watch(formName('destination.field'));
  const [attributeTarget, setAttributeTarget] = React.useState<
    Attribute | null | undefined
  >(undefined);

  const scopeFormName = formName('destination.scope');
  const getScopeFormValue = () => getFormValue('destination.scope');
  const localeFormName = formName('destination.locale');
  const getLocaleFormValue = () => getFormValue('destination.locale');
  const unitFormName = formName('destination.unit');
  const getUnitFormValue = () => getFormValue('destination.unit');
  const currencyFormName = formName('destination.currency');
  const getCurrencyFormValue = () => getFormValue('destination.currency');
  const roundPrecisionFormName = formName('round_precision');
  const getRoundPrecisionFormValue = () => getFormValue('round_precision');
  const operationListFormName = formName('operation_list');
  const getOperationListFormValue = () => getFormValue('operation_list');
  const sourceFormName = formName('source');
  const getSourceFormValue = () => getFormValue('source');

  const getAvailableLocalesForTarget = (): Locale[] => {
    if (!attributeTarget?.scopable) {
      return locales;
    }
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };
  const getAvailableCurrenciesForTarget = (
    currencies: IndexedCurrencies
  ): Currency[] => {
    if (!attributeTarget?.scopable) {
      return Object.values(currencies);
    }
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].currencies.map(code => ({ code }));
    }
    return [];
  };

  const handleTargetChange = (attributeCode: AttributeCode) => {
    const getAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await fetchAttribute(router, attributeCode);
      setAttributeTarget(attribute);
      setValue(formName('destination.field'), attribute?.code);
    };
    getAttribute(attributeCode);
  };

  useGetAttributeAtMount(
    getFormValue('destination.field'),
    router,
    attributeTarget,
    (attribute: Attribute | null | undefined) => {
      if (attribute || attribute === null) {
        setAttributeTarget(attribute);
        setValue(formName('destination.field'), attribute?.code);
      }
    }
  );

  const isTargetDisabled = () => null === attributeTarget;

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.actions.calculate.title')}
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}
      lineNumber={lineNumber}>
      <Controller
        name={typeFormName}
        as={<span hidden />}
        defaultValue='calculate'
      />
      <Controller
        name={sourceFormName}
        as={<span hidden />}
        defaultValue={getSourceFormValue()}
      />
      <Controller
        name={operationListFormName}
        as={<span hidden />}
        defaultValue={getOperationListFormValue()}
      />
      <Controller
        name={formName('destination.field')}
        as={<span hidden />}
        defaultValue={getFormValue('destination.field')}
      />
      <CalculateActionGrid>
        <ActionLeftSide>
          <FallbackField
            field={getFormValue('destination.field')}
            scope={getFormValue('destination.scope') || null}
            locale={getFormValue('destination.locale') || null}
          />
          &nbsp;is calculated&nbsp;
          {getOperationListFormValue().map(
            (operation: Operation, key: number) => (
              <React.Fragment key={key}>
                {Operator.ADD === operation.operator && (
                  <AddView
                    operand={operation}
                    source={key === 0 ? getSourceFormValue() : null}
                  />
                )}
                {Operator.SUBSTRACT === operation.operator && (
                  <SubstractView
                    operand={operation}
                    source={key === 0 ? getSourceFormValue() : null}
                  />
                )}
                {Operator.MULTIPLY === operation.operator && (
                  <MultiplyView
                    operand={operation}
                    source={key === 0 ? getSourceFormValue() : null}
                  />
                )}
                {Operator.DIVIDE === operation.operator && (
                  <DivideView
                    operand={operation}
                    source={key === 0 ? getSourceFormValue() : null}
                  />
                )}

                {key < getOperationListFormValue().length - 1 && ', then '}
              </React.Fragment>
            )
          )}
          .
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate(
              'pimee_catalog_rule.form.edit.actions.calculate.select_target'
            )}
          </ActionTitle>
          <ActionFormContainer>
            <SelectorBlock
              className={
                null === attributeTarget ? 'select2-container-error' : ''
              }>
              <AttributeSelector
                data-testid={`edit-rules-action-${lineNumber}-destination-field`}
                name={formName('destination.field')}
                label={`${translate(
                  'pimee_catalog_rule.form.edit.fields.attribute'
                )} ${translate('pim_common.required_label')}`}
                currentCatalogLocale={currentCatalogLocale}
                value={attributeTarget?.code || null}
                onChange={handleTargetChange}
                placeholder={translate(
                  'pimee_catalog_rule.form.edit.actions.calculate.attribute_placeholder'
                )}
                filterAttributeTypes={targetAttributeTypes}
              />
              {null === attributeTarget && (
                <ErrorBlock>
                  <InlineHelper danger>
                    {`${translate(
                      'pimee_catalog_rule.exceptions.unknown_attribute'
                    )} ${translate(
                      'pimee_catalog_rule.exceptions.select_another_attribute'
                    )} ${translate('pimee_catalog_rule.exceptions.or')} `}
                    <a
                      href={`#${router.generate(
                        `pim_enrich_attribute_create`
                      )}`}>
                      {translate(
                        'pimee_catalog_rule.exceptions.create_attribute_link'
                      )}
                    </a>
                  </InlineHelper>
                </ErrorBlock>
              )}
            </SelectorBlock>
            {attributeTarget?.type === AttributeType.METRIC && (
              <SelectorBlock
                className={
                  isFormFieldInError('destination.unit')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={MeasurementUnitSelector}
                  data-testid={`edit-rules-action-${lineNumber}-destination-unit`}
                  name={unitFormName}
                  attribute={attributeTarget}
                  value={getUnitFormValue()}
                  rules={getMeasurementUnitValidator(
                    attributeTarget,
                    router,
                    translate
                  )}
                />
              </SelectorBlock>
            )}
            {attributeTarget?.type === AttributeType.PRICE_COLLECTION && (
              <SelectorBlock
                className={
                  isFormFieldInError('destination.currency')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={CurrencySelector}
                  data-testid={`edit-rules-action-${lineNumber}-destination-currency`}
                  name={currencyFormName}
                  availableCurrencies={getAvailableCurrenciesForTarget(
                    currencies
                  )}
                  value={getCurrencyFormValue()}
                  rules={getCurrencyValidator(
                    attributeTarget,
                    translate,
                    currentCatalogLocale,
                    getAvailableCurrenciesForTarget(currencies),
                    currencies,
                    getScopeFormValue()
                  )}
                />
              </SelectorBlock>
            )}
            <SelectorBlock
              className={
                isFormFieldInError('round_precision')
                  ? 'select2-container-error'
                  : ''
              }>
              <Controller
                as={InputNumber}
                data-testid={`edit-rules-action-${lineNumber}-round-precision`}
                name={roundPrecisionFormName}
                label={translate(
                  'pimee_catalog_rule.form.edit.actions.calculate.round_precision'
                )}
                value={getRoundPrecisionFormValue()}
                onChange={([event]) => {
                  const value = event.target.value;
                  return value === '' ? null : parseInt(value);
                }}
                small
              />
            </SelectorBlock>
            {attributeTarget?.scopable && (
              <SelectorBlock
                className={
                  isFormFieldInError('destination.scope')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={ScopeSelector}
                  data-testid={`edit-rules-action-${lineNumber}-destination-scope`}
                  name={scopeFormName}
                  label={`${translate(
                    'pim_enrich.entity.channel.uppercase_label'
                  )} ${translate('pim_common.required_label')}`}
                  availableScopes={Object.values(scopes)}
                  currentCatalogLocale={currentCatalogLocale}
                  value={getScopeFormValue()}
                  allowClear={!attributeTarget?.scopable}
                  disabled={isTargetDisabled()}
                  rules={getScopeValidation(
                    attributeTarget,
                    scopes,
                    translate,
                    currentCatalogLocale
                  )}
                />
              </SelectorBlock>
            )}
            {attributeTarget?.localizable && (
              <SelectorBlock
                className={
                  isFormFieldInError('destination.locale')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={LocaleSelector}
                  data-testid={`edit-rules-action-${lineNumber}-destination-locale`}
                  name={localeFormName}
                  availableLocales={getAvailableLocalesForTarget()}
                  label={`${translate(
                    'pim_enrich.entity.locale.uppercase_label'
                  )} ${translate('pim_common.required_label')}`}
                  value={getLocaleFormValue()}
                  allowClear={!attributeTarget?.localizable}
                  rules={getLocaleValidation(
                    attributeTarget,
                    locales,
                    getAvailableLocalesForTarget(),
                    getScopeFormValue(),
                    translate,
                    currentCatalogLocale
                  )}
                  disabled={isTargetDisabled()}
                />
              </SelectorBlock>
            )}
          </ActionFormContainer>
        </ActionRightSide>
      </CalculateActionGrid>
    </ActionTemplate>
  );
};

export { CalculateActionLine };
