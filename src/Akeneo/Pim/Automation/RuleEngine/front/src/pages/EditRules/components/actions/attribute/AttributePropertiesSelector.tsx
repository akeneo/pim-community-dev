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
import { validateAttribute } from './attribute.utils';

type Props = {
  baseFormName: string;
  operationLineNumber: number;
  attributeCode: AttributeCode;
  locales: Locale[];
  scopes: IndexedScopes;
  isCurrencyRequired: boolean;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  baseFormName,
  operationLineNumber,
  attributeCode,
  scopes,
  locales,
  isCurrencyRequired,
}) => {
  const translate = useTranslate();
  const { watch, errors, setValue, } = useFormContext();
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const currencies = useActiveCurrencies();
  const [attribute, setAttribute] = React.useState<Attribute | null | undefined>();
  const labelLocaleFormName = `${baseFormName}.label_locale`;
  const fieldFormName = `${baseFormName}.field`;
  const localeFormName = `${baseFormName}.locale`;
  const scopeFormName = `${baseFormName}.scope`;
  const currencyFormName = `${baseFormName}.currency`;

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
    const scopeCode = watch(scopeFormName);
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].currencies.map(code => ({ code }));
    }
    return [];
  };

  const getAvailableLocales = (): Locale[] => {
    if (!attribute?.scopable) {
      return locales;
    }
    const scopeCode = watch(scopeFormName);
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
      {[
        AttributeType.OPTION_MULTI_SELECT,
        AttributeType.OPTION_SIMPLE_SELECT,
        AttributeType.REFERENCE_ENTITY_COLLECTION,
        AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT
      ].includes(attribute?.type as AttributeType) && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-elementLocale' +
            (isFullFormFieldInError(labelLocaleFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-label-locale`}
            as={<input type='hidden' />}
            name={labelLocaleFormName}
          />
          <LocaleSelector
            allowClear={true}
            availableLocales={locales}
            value={watch(labelLocaleFormName)}
            hiddenLabel
            placeholder={translate('TODO Label Locale')}
            onChange={(localeCode: LocaleCode) => { setValue(labelLocaleFormName, localeCode); }}
          />
        </span>
      )}
      {AttributeType.DATE === attribute?.type && (
        <span>Option format (optional)</span>
      )}
      {AttributeType.PRICE_COLLECTION === attribute?.type && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-element-currency' +
            (isFullFormFieldInError(currencyFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            as={<input type='hidden' />}
            name={currencyFormName}
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-currency`}
            rules={getCurrencyValidation(
              attribute,
              translate,
              currentCatalogLocale,
              getAvailableCurrencies(currencies),
              currencies,
              watch(scopeFormName) ?? watch(scopeFormName),
              isCurrencyRequired
            )}
          />
          <CurrencySelector
            availableCurrencies={getAvailableCurrencies(currencies)}
            value={watch(currencyFormName)}
            hiddenLabel
            onChange={(currencyCode: CurrencyCode) => { setValue(currencyFormName, currencyCode); }}
            allowClear={!isCurrencyRequired}
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
            as={<input type='hidden' />}
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-scope`}
            name={scopeFormName}
            rules={getScopeValidation(
              attribute,
              scopes,
              translate,
              currentCatalogLocale
            )}
          />
          <ScopeSelector
            allowClear={false}
            availableScopes={Object.values(scopes)}
            value={watch(scopeFormName)}
            currentCatalogLocale={currentCatalogLocale}
            hiddenLabel
            onChange={(scopeCode: ScopeCode) => { setValue(scopeFormName, scopeCode); }}
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
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-locale`}
            as={<input type='hidden' />}
            name={localeFormName}
            rules={getLocaleValidation(
              attribute,
              locales,
              getAvailableLocales(),
              watch(scopeFormName),
              translate,
              currentCatalogLocale
            )}
          />
          <LocaleSelector
            allowClear={false}
            availableLocales={getAvailableLocales()}
            value={watch(localeFormName)}
            hiddenLabel
            onChange={(localeCode: LocaleCode) => { setValue(localeFormName, localeCode); }}
          />
        </span>
      )}
    </>
  );
};

export { AttributePropertiesSelector };
