import React, { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import {
  Attribute,
  AttributeCode,
  AttributeType,
  getAttributeLabel,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../../models';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../../components/Selectors/ScopeSelector';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../../components/Selectors/LocaleSelector';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import {
  CurrencySelector,
  getCurrencyValidation,
} from '../../../../../components/Selectors/CurrencySelector';
import { IndexedCurrencies } from '../../../../../repositories/CurrencyRepository';
import { Currency, CurrencyCode } from '../../../../../models/Currency';
import { useActiveCurrencies } from '../../../hooks/useActiveCurrencies';
import { Router } from '../../../../../dependenciesTools';
import { getAttributeByIdentifier } from '../../../../../repositories/AttributeRepository';
import get from 'lodash/get';
import { validateAttribute } from '../attribute/attribute.utils';

type Props = {
  operationLineNumber: number;
  attributeCode: AttributeCode;
  fieldFormName: string;
  scopeFormName: string;
  localeFormName: string;
  currencyFormName: string;
  locales: Locale[];
  scopes: IndexedScopes;
  defaultLocale?: LocaleCode;
  defaultScope?: ScopeCode;
  defaultCurrency: CurrencyCode;
  onCurrencyChange: (currencyCode: CurrencyCode) => void;
  onScopeChange: (scopeCode: ScopeCode) => void;
  onLocaleChange: (localeCode: LocaleCode) => void;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  operationLineNumber,
  attributeCode,
  fieldFormName,
  scopeFormName,
  localeFormName,
  currencyFormName,
  scopes,
  locales,
  defaultLocale,
  defaultScope,
  defaultCurrency,
  onCurrencyChange,
  onScopeChange,
  onLocaleChange,
}) => {
  const translate = useTranslate();
  const { watch, errors } = useFormContext();
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const currencies = useActiveCurrencies();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >();

  useEffect(() => {
    const getAttribute = async (
      router: Router,
      attributeCode: AttributeCode
    ) => {
      const attribute = await getAttributeByIdentifier(attributeCode, router);
      setAttribute(attribute);
    };
    getAttribute(router, attributeCode);
  }, [attributeCode]);

  const getAvailableCurrencies = (
    currencies: IndexedCurrencies
  ): Currency[] => {
    if (!attribute?.scopable) {
      return Object.values(currencies);
    }
    // watch() is needed instead of getFormValue() when currencySelector is displayed before ScopeSelector
    const scopeCode = watch(scopeFormName) ?? defaultScope;
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].currencies.map(code => ({ code }));
    }
    return [];
  };

  const getAvailableLocales = (): Locale[] => {
    if (!attribute?.scopable) {
      return locales;
    }
    const scopeCode = watch(scopeFormName) ?? defaultScope;
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };

  const isFullFormFieldInError = (fullFormName: string): boolean => {
    const error = get(errors, fullFormName);
    return 'undefined' !== typeof error;
  };

  return (
    <>
      <span className={'AknRuleOperation-element'}>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue={attributeCode}
          rules={{ validate: validateAttribute(translate, router) }}
        />
        <span
          className={
            'AknRuleOperation-elementField' +
            (isFullFormFieldInError(fieldFormName)
              ? ' AknRuleOperation-elementField--error'
              : '')
          }>
          {null === attribute && `[${attributeCode}]`}
          {attribute && getAttributeLabel(attribute, currentCatalogLocale)}
        </span>
      </span>
      {AttributeType.PRICE_COLLECTION === attribute?.type && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-element-currency' +
            (isFullFormFieldInError(currencyFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            as={<input type="hidden"/>}
            name={currencyFormName}
            rules={getCurrencyValidation(
              attribute,
              translate,
              currentCatalogLocale,
              getAvailableCurrencies(currencies),
              currencies,
              watch(scopeFormName) ?? defaultScope
            )}
          />
          <CurrencySelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-currency`}
            availableCurrencies={getAvailableCurrencies(currencies)}
            value={defaultCurrency}
            hiddenLabel
            onChange={onCurrencyChange}
          />
        </span>
      )}
      {attribute?.scopable && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-elementScope' +
            (isFullFormFieldInError(scopeFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            as={<input type="hidden"/>}
            name={scopeFormName}
            rules={getScopeValidation(
              attribute,
              scopes,
              translate,
              currentCatalogLocale
            )}
          />
          <ScopeSelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-scope`}
            allowClear={false}
            availableScopes={Object.values(scopes)}
            value={defaultScope}
            currentCatalogLocale={currentCatalogLocale}
            hiddenLabel
            onChange={onScopeChange}
          />
        </span>
      )}
      {attribute?.localizable && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-elementLocale' +
            (isFullFormFieldInError(localeFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            as={<input type="hidden"/>}
            name={localeFormName}
            rules={getLocaleValidation(
              attribute,
              locales,
              getAvailableLocales(),
              watch(scopeFormName) ?? defaultScope,
              translate,
              currentCatalogLocale
            )}
          />
          <LocaleSelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-locale`}
            allowClear={false}
            availableLocales={getAvailableLocales()}
            value={defaultLocale}
            hiddenLabel
            onChange={onLocaleChange}
          />
        </span>
      )}
    </>
  );
};

export { AttributePropertiesSelector };
