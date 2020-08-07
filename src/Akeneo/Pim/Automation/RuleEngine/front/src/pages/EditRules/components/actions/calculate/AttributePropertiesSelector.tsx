import React from 'react';
import {
  Attribute,
  AttributeType,
  getAttributeLabel,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../../models';
import { ScopeSelector } from '../../../../../components/Selectors/ScopeSelector';
import { useGetAttributeAtMount } from '../attribute/attribute.utils';
import {
  useBackboneRouter,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { LocaleSelector } from '../../../../../components/Selectors/LocaleSelector';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { CurrencySelector } from '../../../../../components/Selectors/CurrencySelector';
import { IndexedCurrencies } from '../../../../../repositories/CurrencyRepository';
import { Currency, CurrencyCode } from '../../../../../models/Currency';
import { useActiveCurrencies } from '../../../hooks/useActiveCurrencies';

type Props = {
  operationLineNumber: number;
  attributeCode: string;
  scopeCode?: ScopeCode | null;
  localeCode?: LocaleCode | null;
  currencyCode?: CurrencyCode | null;
  locales: Locale[];
  scopes: IndexedScopes;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  operationLineNumber,
  attributeCode,
  scopeCode,
  localeCode,
  currencyCode,
  scopes,
  locales,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const currencies = useActiveCurrencies();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >();
  useGetAttributeAtMount(attributeCode, router, attribute, setAttribute);

  const getAvailableCurrenciesForTarget = (
    currencies: IndexedCurrencies
  ): Currency[] => {
    if (!attribute?.scopable) {
      return Object.values(currencies);
    }
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].currencies.map(code => ({ code }));
    }
    return [];
  };

  return (
    <>
      <span className={'AknRuleOperation-element'}>
        <span className={'AknRuleOperation-elementField'}>
          {null === attribute && `[${attributeCode}]`}
          {attribute && getAttributeLabel(attribute, currentCatalogLocale)}
        </span>
      </span>
      {AttributeType.PRICE_COLLECTION === attribute?.type && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-element-currency'
          }>
          <CurrencySelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-price`}
            availableCurrencies={getAvailableCurrenciesForTarget(currencies)}
            name={`edit-rules-action-operation-list-${operationLineNumber}-currency`}
            value={currencyCode || undefined}
            hiddenLabel
          />
        </span>
      )}
      {attribute?.scopable && (
        <span
          className={'AknRuleOperation-element AknRuleOperation-elementScope'}>
          <ScopeSelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-scope`}
            allowClear={false}
            availableScopes={Object.values(scopes)}
            value={scopeCode || undefined}
            name={`edit-rules-action-operation-list-${operationLineNumber}-scope`}
            currentCatalogLocale={currentCatalogLocale}
            hiddenLabel
          />
        </span>
      )}
      {attribute?.localizable && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-elementLocale'
          }>
          <LocaleSelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-locale`}
            allowClear={false}
            availableLocales={locales}
            value={localeCode || undefined}
            name={`edit-rules-action-operation-list-${operationLineNumber}-locale`}
            hiddenLabel
          />
        </span>
      )}
    </>
  );
};

export { AttributePropertiesSelector };
