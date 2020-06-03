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
import {
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { LineErrors } from '../LineErrors';
import { useRegisterConst } from "../../hooks/useRegisterConst";
import { useTranslate } from "../../../../dependenciesTools/hooks";

const shouldDisplayValue: (operator: Operator) => boolean = operator =>
  !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
    operator
  );

type AttributeConditionLineProps = {
  condition: TextAttributeCondition | MultiOptionsAttributeCondition;
  lineNumber: number;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  availableOperators: Operator[];
};

const AttributeConditionLine: React.FC<AttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
  availableOperators,
  children,
}) => {
  const translate = useTranslate();
  const { watch, setValue } = useFormContext();

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);

  const getScopeFormValue: () => ScopeCode = () =>
    watch(`content.conditions[${lineNumber}].scope`);
  const getLocaleFormValue: () => LocaleCode = () =>
    watch(`content.conditions[${lineNumber}].locale`);
  const setLocaleFormValue = (locale: LocaleCode | null) => {
    setValue(`content.conditions[${lineNumber}].locale`, locale);
  }

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

  useRegisterConst(`content.conditions[${lineNumber}].field`, condition.field);

  const handleScopeChange = () => {
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
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
          hiddenLabel={true}
          availableOperators={availableOperators}
          value={condition.operator}
          name={`content.conditions[${lineNumber}].operator`}
        />
      </OperatorColumn>
      <ValueColumn>
        {shouldDisplayValue(getOperatorFormValue()) && children}
      </ValueColumn>
      <ScopeColumn>
        {(condition.attribute.scopable || getScopeFormValue()) && (
          <ScopeSelector
            hiddenLabel={true}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={condition.scope}
            onChange={handleScopeChange}
            allowClear={!condition.attribute.scopable}
            name={`content.conditions[${lineNumber}].scope`}
            validation={scopeValidation}
          />
        )}
      </ScopeColumn>
      <LocaleColumn>
        {(condition.attribute.localizable || getLocaleFormValue()) && (
          <LocaleSelector
            hiddenLabel={true}
            availableLocales={getAvailableLocales()}
            value={condition.locale}
            allowClear={!condition.attribute.localizable}
            name={`content.conditions[${lineNumber}].locale`}
            validation={localeValidation}
          />
        )}
      </LocaleColumn>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </div>
  );
};

export { AttributeConditionLine };
