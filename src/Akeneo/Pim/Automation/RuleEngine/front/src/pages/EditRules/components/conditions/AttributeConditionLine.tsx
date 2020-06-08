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
  ConditionErrorLine,
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { LineErrors } from '../LineErrors';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models/Attribute';

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
  attribute?: Attribute | null;
};

const AttributeConditionLine: React.FC<AttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
  availableOperators,
  children,
  attribute,
}) => {
  const translate = useTranslate();
  const { watch } = useFormContext();

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);
  const getScopeFormValue: () => ScopeCode = () =>
    watch(`content.conditions[${lineNumber}].scope`);
  const getLocaleFormValue: () => LocaleCode = () =>
    watch(`content.conditions[${lineNumber}].locale`);

  const getAvailableLocales = (): Locale[] => {
    if (!attribute || !attribute.scopable) {
      return locales;
    }

    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }

    return [];
  };

  const getLocaleValidation = () => {
    const localeValidation: any = {};
    if (attribute && attribute.localizable) {
      localeValidation['required'] = translate(
        'pimee_catalog_rule.exceptions.required_locale'
      );
    }
    localeValidation['validate'] = (localeCode: any) => {
      if (attribute && attribute.localizable) {
        if (!locales.some(locale => locale.code === localeCode)) {
          return translate(
            'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
            { localeCode }
          );
        }
        if (!getAvailableLocales().some(locale => locale.code === localeCode)) {
          return attribute.scopable
            ? translate('pimee_catalog_rule.exceptions.unbound_locale', {
                localeCode,
                channelCode: getScopeFormValue(),
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

    return localeValidation;
  };

  const getScopeValidation = () => {
    const scopeValidation: any = {};
    if (attribute && attribute.scopable) {
      scopeValidation['required'] = translate(
        'pimee_catalog_rule.exceptions.required_scope'
      );
    }
    scopeValidation['validate'] = (scopeCode: any) => {
      if (attribute && attribute.scopable) {
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

    return scopeValidation;
  };
  const [localeValidation, setLocaleValidation] = React.useState(
    getLocaleValidation()
  );
  const [scopeValidation, setScopeValidation] = React.useState(
    getScopeValidation()
  );
  React.useEffect(() => {
    setLocaleValidation(getLocaleValidation());
    setScopeValidation(getScopeValidation());
  }, [JSON.stringify(getAvailableLocales())]);

  useRegisterConst(`content.conditions[${lineNumber}].field`, condition.field);

  const handleScopeChange = () => {
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      condition.locale = undefined;
    }
  };

  const title =
    attribute && attribute.labels[currentCatalogLocale]
      ? attribute.labels[currentCatalogLocale]
      : '[' + condition.field + ']';

  if (attribute === undefined) {
    return (
      <div className='AknGrid-bodyCell'>
        <img
          src='/bundles/pimui/images//loader-V2.svg'
          alt={translate('pim_common.loading')}
        />
      </div>
    );
  }

  if (attribute === null) {
    return (
      <div className='AknGrid-bodyCell'>
        <ConditionErrorLine>
          {translate('pimee_catalog_rule.exceptions.unknown_attribute', {
            attributeCode: condition.field,
          })}
        </ConditionErrorLine>
      </div>
    );
  }

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn className={'AknGrid-bodyCell--highlight'} title={title}>
        {title}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          data-testid={`edit-rules-input-${lineNumber}-operator`}
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
        {(attribute.scopable || getScopeFormValue()) && (
          <ScopeSelector
            data-testid={`edit-rules-input-${lineNumber}-scope`}
            hiddenLabel={true}
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={condition.scope}
            onChange={handleScopeChange}
            allowClear={!attribute.scopable}
            name={`content.conditions[${lineNumber}].scope`}
            validation={scopeValidation}
          />
        )}
      </ScopeColumn>
      <LocaleColumn>
        {(attribute.localizable || getLocaleFormValue()) && (
          <LocaleSelector
            data-testid={`edit-rules-input-${lineNumber}-locale`}
            hiddenLabel={true}
            availableLocales={getAvailableLocales()}
            value={condition.locale}
            allowClear={!attribute.localizable}
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
