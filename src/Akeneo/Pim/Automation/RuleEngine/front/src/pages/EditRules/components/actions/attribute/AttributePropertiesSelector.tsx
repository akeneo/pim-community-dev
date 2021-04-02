import React, {useEffect} from 'react';
import {Controller, useFormContext} from 'react-hook-form';
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
import {IndexedScopes} from '../../../../../repositories/ScopeRepository';
import {
  CurrencySelector,
  getCurrencyValidation,
} from '../../../../../components/Selectors/CurrencySelector';
import {IndexedCurrencies} from '../../../../../repositories/CurrencyRepository';
import {Currency, CurrencyCode} from '../../../../../models/Currency';
import {useActiveCurrencies} from '../../../hooks/useActiveCurrencies';
import {Router} from '../../../../../dependenciesTools';
import {getAttributeByIdentifier} from '../../../../../repositories/AttributeRepository';
import get from 'lodash/get';
import {validateAttribute} from './attribute.utils';
import {DateFormatSelector} from '../../../../../components/Selectors/DateFormatSelector';

type Props = {
  baseFormName: string;
  operationLineNumber: number;
  attributeCode: AttributeCode;
  locales: Locale[];
  uiLocales: Locale[];
  scopes: IndexedScopes;
  isCurrencyRequired: boolean;
  context?: string;
};

const AttributePropertiesSelector: React.FC<Props> = ({
  baseFormName,
  operationLineNumber,
  attributeCode,
  scopes,
  locales,
  uiLocales,
  isCurrencyRequired,
  context,
}) => {
  const translate = useTranslate();
  const {watch, errors, setValue} = useFormContext();
  const currentCatalogLocale = useUserCatalogLocale();
  const router = useBackboneRouter();
  const currencies = useActiveCurrencies();
  const [attribute, setAttribute] = React.useState<
    Attribute | null | undefined
  >();
  const labelLocaleFormName = `${baseFormName}.label_locale`;
  const fieldFormName = `${baseFormName}.field`;
  const localeFormName = `${baseFormName}.locale`;
  const scopeFormName = `${baseFormName}.scope`;
  const currencyFormName = `${baseFormName}.currency`;
  const formatFormName = `${baseFormName}.format`;
  const unitLabelLocaleFormName = `${baseFormName}.unit_label_locale`;

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
      return scopes[scopeCode].currencies.map(code => ({code}));
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

  const hasOptions: boolean =
    [
      AttributeType.OPTION_SIMPLE_SELECT,
      AttributeType.OPTION_MULTI_SELECT,
      AttributeType.REFERENCE_ENTITY_COLLECTION,
      AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
      AttributeType.DATE,
      AttributeType.PRICE_COLLECTION,
    ].includes(attribute?.type as AttributeType) ||
    ('concatenate' === context && AttributeType.METRIC === attribute?.type);

  return (
    <>
      <span
        className={`AknRuleOperation-element${
          hasOptions ? ' AknRuleOperation-element--glued' : ''
        }`}>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue={attributeCode}
          rules={{validate: validateAttribute(translate, router)}}
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
        AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
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
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.concatenate.label_locale'
            )}
            onChange={(localeCode: LocaleCode) => {
              setValue(
                labelLocaleFormName,
                localeCode != '' ? localeCode : undefined
              );
            }}
            containerCssClass={`select2-container-left-glued select2-container-as-option select2-container-uppercase select2-container-operation-field-option`}
            displayAsCode={true}
          />
        </span>
      )}
      {AttributeType.DATE === attribute?.type && (
        <>
          <Controller
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-format`}
            as={<input type='hidden' />}
            name={formatFormName}
          />
          <DateFormatSelector
            value={watch(formatFormName)}
            defaultFormat={'Y-m-d'}
            predefinedFormats={{
              'Y-m-d': '(1999-08-03)',
              'd/m/y': '(03/08/99)',
              'd.m.y': '(03.08.99)',
              'm/d/Y': '(08/03/1999)',
              'd-M-Y': '(03-Aug-1999)',
              'n/d/y': '(8/03/99)',
              'j/m/y': '(3/08/99)',
            }}
            onChange={(dateFormat: string) => {
              setValue(
                formatFormName,
                dateFormat !== '' ? dateFormat : undefined
              );
            }}
          />
        </>
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
              watch(scopeFormName),
              isCurrencyRequired
            )}
          />
          <CurrencySelector
            availableCurrencies={getAvailableCurrencies(currencies)}
            value={watch(currencyFormName)}
            hiddenLabel
            onChange={(currencyCode: CurrencyCode) => {
              setValue(
                currencyFormName,
                currencyCode !== '' ? currencyCode : undefined
              );
            }}
            allowClear={!isCurrencyRequired}
            containerCssClass={`select2-container-left-glued select2-container-as-option select2-container-uppercase select2-container-operation-field-option`}
          />
        </span>
      )}
      {'concatenate' === context && AttributeType.METRIC === attribute?.type && (
        <span
          className={
            'AknRuleOperation-element AknRuleOperation-elementLocale' +
            (isFullFormFieldInError(unitLabelLocaleFormName)
              ? ' select2-container-error'
              : '')
          }>
          <Controller
            as={<input type='hidden' />}
            name={unitLabelLocaleFormName}
          />
          <LocaleSelector
            data-testid={`edit-rules-action-operation-list-${operationLineNumber}-unit-locale`}
            allowClear={true}
            availableLocales={uiLocales}
            value={watch(unitLabelLocaleFormName)}
            hiddenLabel
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.concatenate.unit_label_locale'
            )}
            onChange={(localeCode: LocaleCode) => {
              setValue(
                unitLabelLocaleFormName,
                localeCode != '' ? localeCode : undefined
              );
            }}
            containerCssClass={`select2-container-left-glued select2-container-as-option select2-container-uppercase select2-container-operation-field-option`}
            displayAsCode={true}
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
            onChange={(scopeCode: ScopeCode) => {
              setValue(scopeFormName, scopeCode);
            }}
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
            onChange={(localeCode: LocaleCode) => {
              setValue(localeFormName, localeCode);
            }}
          />
        </span>
      )}
    </>
  );
};

export {AttributePropertiesSelector};
