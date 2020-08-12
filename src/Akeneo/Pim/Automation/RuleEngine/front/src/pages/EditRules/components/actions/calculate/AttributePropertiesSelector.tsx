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
import { ScopeSelector } from '../../../../../components/Selectors/ScopeSelector';
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
import { Router } from '../../../../../dependenciesTools';
import { getAttributeByIdentifier } from '../../../../../repositories/AttributeRepository';

type Props = {
  operationLineNumber: number;
  attributeCode: AttributeCode;
  scopeFormName: string;
  localeFormName: string;
  currencyFormName: string;
  locales: Locale[];
  scopes: IndexedScopes;
  defaultLocale?: LocaleCode;
  defaultScope?: ScopeCode;
  defaultCurrency?: CurrencyCode;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  operationLineNumber,
  attributeCode,
  scopeFormName,
  localeFormName,
  currencyFormName,
  scopes,
  locales,
  defaultLocale,
  defaultScope,
  defaultCurrency,
}) => {
  const { watch } = useFormContext();
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

  const getAvailableCurrenciesForAttribute = (
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
          <Controller
            as={CurrencySelector}
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-currency`}
            availableCurrencies={getAvailableCurrenciesForAttribute(currencies)}
            name={currencyFormName}
            value={defaultCurrency}
            defaultValue={defaultCurrency}
            hiddenLabel
          />
        </span>
      )}
      {attribute?.scopable && (
        <span
          className={'AknRuleOperation-element AknRuleOperation-elementScope'}>
          <Controller
            as={ScopeSelector}
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-scope`}
            allowClear={false}
            availableScopes={Object.values(scopes)}
            value={defaultScope}
            defaultValue={defaultScope}
            name={scopeFormName}
            currentCatalogLocale={currentCatalogLocale}
            hiddenLabel
          />
        </span>
      )}
      {attribute?.localizable && (
        <span
          className={'AknRuleOperation-element AknRuleOperation-elementLocale'}>
          <Controller
            as={LocaleSelector}
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-locale`}
            allowClear={false}
            availableLocales={locales}
            value={defaultLocale}
            defaultValue={defaultLocale}
            name={localeFormName}
            hiddenLabel
          />
        </span>
      )}
    </>
  );
};

export { AttributePropertiesSelector };
