import React, { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import {
  Attribute,
  AttributeCode,
  AttributeType,
  getAttributeLabel,
  Locale,
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
import { Currency } from '../../../../../models/Currency';
import { useActiveCurrencies } from '../../../hooks/useActiveCurrencies';
import { Router } from '../../../../../dependenciesTools';
import { getAttributeByIdentifier } from '../../../../../repositories/AttributeRepository';
import { useControlledFormInputAction } from '../../../hooks';

type Props = {
  lineNumber: number;
  operationLineNumber: number;
  attributeCode: AttributeCode;
  scopeFormName: string;
  localeFormName: string;
  currencyFormName: string;
  locales: Locale[];
  scopes: IndexedScopes;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  lineNumber,
  operationLineNumber,
  attributeCode,
  scopeFormName,
  localeFormName,
  currencyFormName,
  scopes,
  locales,
}) => {
  const { watch } = useFormContext();
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const currencies = useActiveCurrencies();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >();
  const { getFormValue } = useControlledFormInputAction<string>(lineNumber);

  useEffect(() => {
    const getAttribute = async (
      router: Router,
      attributeCode: AttributeCode
    ) => {
      const attribute = await getAttributeByIdentifier(attributeCode, router);
      if (setAttribute && attribute) {
        setAttribute(attribute);
      } else if (setAttribute) {
        setAttribute(null);
      }
    };
    getAttribute(router, attributeCode);
  }, [attributeCode]);

  const getAvailableCurrenciesForTarget = (
    currencies: IndexedCurrencies
  ): Currency[] => {
    if (!attribute?.scopable) {
      return Object.values(currencies);
    }
    // watch() is needed instead of getFormValue() when currencySelector is displayed before ScopeSelector
    const scopeCode = watch(scopeFormName);
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
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-price`}
            availableCurrencies={getAvailableCurrenciesForTarget(currencies)}
            name={currencyFormName}
            value={getFormValue(currencyFormName)}
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
            value={getFormValue(scopeFormName)}
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
            value={getFormValue(localeFormName)}
            name={localeFormName}
            hiddenLabel
          />
        </span>
      )}
    </>
  );
};

export { AttributePropertiesSelector };
