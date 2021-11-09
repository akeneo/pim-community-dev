import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {ConditionLineProps} from './ConditionLineProps';
import {InputNumber} from '../../../../components/Inputs';
import {AttributeConditionLine} from './AttributeConditionLine';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import {
  Attribute,
  PriceCollectionAttributeOperators,
  PriceCollectionAttributeCondition,
} from '../../../../models';
import {Operator} from '../../../../models/Operator';
import {useControlledFormInputCondition} from '../../hooks';
import {useGetAttributeAtMount} from '../actions/attribute/attribute.utils';
import {
  CurrencySelector,
  getCurrencyValidation,
} from '../../../../components/Selectors/CurrencySelector';
import {useActiveCurrencies} from '../../hooks/useActiveCurrencies';
import {IndexedCurrencies} from '../../../../repositories/CurrencyRepository';
import {Currency} from '../../../../models/Currency';

type PriceCollectionAttributeConditionLineProps = ConditionLineProps & {
  condition: PriceCollectionAttributeCondition;
};

const PriceCollectionAttributeConditionLine: React.FC<PriceCollectionAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currencies = useActiveCurrencies();
  const {watch} = useFormContext();

  const {
    valueFormName,
    getValueFormValue,
    amountValueFormName,
    currencyValueFormName,
    getAmountValueFormValue,
    getCurrencyValueFormValue,
    isFormFieldInError,
    scopeFormName,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  useGetAttributeAtMount(condition.field, router, attribute, setAttribute);

  const getAvailableCurrencies = (
    currencies: IndexedCurrencies
  ): Currency[] => {
    if (!attribute?.scopable) {
      return Object.values(currencies);
    }
    // watch() is needed instead of getFormValue() when currencySelector is displayed before ScopeSelector
    const scopeCode = watch(scopeFormName) ?? condition.scope;
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].currencies.map(code => ({code}));
    }
    return Object.values(currencies);
  };

  return (
    <AttributeConditionLine
      attribute={attribute}
      availableOperators={PriceCollectionAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}>
      {/* This controller is only here to handle correctly the line removing in react hook form */}
      <Controller
        as={<span hidden />}
        name={valueFormName}
        defaultValue={getValueFormValue()}
      />
      <Controller
        as={InputNumber}
        className={`AknTextField AknNumberField AknNumberField--hideArrows AknTextField--glued${
          isFormFieldInError('value.amount') ||
          isFormFieldInError('value.currency')
            ? ' AknTextField--error'
            : ''
        }`}
        data-testid={`edit-rules-input-${lineNumber}-amount-value`}
        name={amountValueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
        defaultValue={getAmountValueFormValue()}
        step={attribute?.decimals_allowed ? 0.01 : 1}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required'),
        }}
      />
      <span
        className={
          isFormFieldInError('value.amount') ||
          isFormFieldInError('value.currency')
            ? ' select2-glued-container-error'
            : ''
        }>
        <Controller
          as={CurrencySelector}
          containerCssClass={
            'select2-container-left-glued select2-container-as-option select2-container-uppercase'
          }
          data-testid={`edit-rules-input-${lineNumber}-currency-value`}
          name={currencyValueFormName}
          label={translate('pimee_catalog_rule.form.edit.fields.currency')}
          hiddenLabel={true}
          defaultValue={getCurrencyValueFormValue()}
          availableCurrencies={getAvailableCurrencies(currencies)}
          rules={
            attribute
              ? getCurrencyValidation(
                  attribute,
                  translate,
                  currentCatalogLocale,
                  scopes,
                  currencies,
                  () => watch(scopeFormName) ?? condition.scope
                )
              : undefined
          }
        />
      </span>
    </AttributeConditionLine>
  );
};

export {PriceCollectionAttributeConditionLine};
