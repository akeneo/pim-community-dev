import React from 'react';
import { useFormContext } from 'react-hook-form';
import { Operator } from '../../../../models/Operator';
import {
  Locale,
  LocaleCode,
  MultiOptionsAttributeCondition,
  ScopeCode,
  TextAttributeCondition,
} from '../../../../models';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import {
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';
import { ConditionLineErrors } from './ConditionLineErrors';
import { Translate } from '../../../../dependenciesTools';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';

const shouldDisplayValue: (operator: Operator) => boolean = operator =>
  !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
    operator
  );

type AttributeConditionLineProps = {
  condition: TextAttributeCondition | MultiOptionsAttributeCondition;
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  availableOperators: Operator[];
  setValueFormValue: (value: any) => void;
};

const AttributeConditionLine: React.FC<AttributeConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
  availableOperators,
  children,
  setValueFormValue,
}) => {
  const { watch, setValue, triggerValidation } = useFormContext();

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);

  const getScopeFormValue: () => ScopeCode = () =>
    watch(`content.conditions[${lineNumber}].scope`);
  const getLocaleFormValue: () => LocaleCode = () =>
    watch(`content.conditions[${lineNumber}].locale`);

  const getAvailableLocales = (): Locale[] => {
    if (!condition.attribute.scopable) {
      return locales;
    }

    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }

    return [];
  };

  const localeValidation: any = {};
  if (condition.attribute.localizable) {
    localeValidation['required'] = translate(
      'pimee_catalog_rule.exceptions.required_locale'
    );
  }
  localeValidation['validate'] = (localeCode: any) => {
    if (condition.attribute.localizable) {
      if (!locales.some(locale => locale.code === localeCode)) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
          { localeCode }
        );
      }
      if (!getAvailableLocales().some(locale => locale.code === localeCode)) {
        return condition.attribute.scopable
          ? translate('pimee_catalog_rule.exceptions.unbound_locale', {
              localeCode,
              scopeCode: getScopeFormValue(),
            })
          : translate(
              'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
              { localeCode }
            );
      }
    } else {
      if (localeCode) {
        return translate(
          'pimee_catalog_rule.exceptions.locale_on_unlocalizable_attribute'
        );
      }
    }
    return true;
  };

  const scopeValidation: any = {};
  if (condition.attribute.scopable) {
    scopeValidation['required'] = translate(
      'pimee_catalog_rule.exceptions.required_scope'
    );
  }
  scopeValidation['validate'] = (scopeCode: any) => {
    if (condition.attribute.scopable) {
      if (!scopes[scopeCode]) {
        return translate('pimee_catalog_rule.exceptions.unknown_scope', {
          scopeCode,
        });
      }
    } else {
      if (scopeCode) {
        return translate(
          'pimee_catalog_rule.exceptions.scope_on_unscopable_attribute'
        );
      }
    }
    return true;
  };

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    {
      field: condition.field,
      operator: condition.operator,
      scope: condition.scope,
      locale: condition.locale,
    },
    {
      scope: scopeValidation,
      locale: localeValidation,
    },
    [condition]
  );

  const setLocaleFormValue = (value: LocaleCode | null) => {
    setValue(`content.conditions[${lineNumber}].locale`, value);
    triggerValidation(`content.conditions[${lineNumber}].locale`);
  };

  const setScopeFormValue = (value: ScopeCode) => {
    setValue(`content.conditions[${lineNumber}].scope`, value);
    triggerValidation(`content.conditions[${lineNumber}].scope`);
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
    }
  };

  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue(getOperatorFormValue())) {
      setValueFormValue(null);
    }
  };

  const title =
    condition.attribute.labels[currentCatalogLocale] ||
    '[' + condition.attribute.code + ']';

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn className={'AknGrid-bodyCell--highlight'} title={title}>
        {title}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          availableOperators={availableOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      <ValueColumn>
        {shouldDisplayValue(getOperatorFormValue()) && children}
      </ValueColumn>
      <ScopeColumn>
        {(condition.attribute.scopable || getScopeFormValue()) && (
          <ScopeSelector
            id={`edit-rules-input-${lineNumber}-scope`}
            label='Scope'
            hiddenLabel={true}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={getScopeFormValue()}
            onChange={setScopeFormValue}
            translate={translate}
            allowClear={!condition.attribute.scopable}
          />
        )}
      </ScopeColumn>
      <LocaleColumn>
        {(condition.attribute.localizable || getLocaleFormValue()) && (
          <LocaleSelector
            id={`edit-rules-input-${lineNumber}-locale`}
            label='Locale'
            hiddenLabel={true}
            availableLocales={getAvailableLocales()}
            value={getLocaleFormValue()}
            onChange={setLocaleFormValue}
            translate={translate}
            allowClear={!condition.attribute.localizable}
          />
        )}
      </LocaleColumn>
      <ConditionLineErrors lineNumber={lineNumber} />
    </div>
  );
};

export { AttributeConditionLine };
