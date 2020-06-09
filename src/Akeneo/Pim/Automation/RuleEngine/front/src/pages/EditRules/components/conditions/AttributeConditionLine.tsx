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
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../components/Selectors/LocaleSelector';
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
import { Attribute } from '../../../../models';

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
  const { watch, setValue } = useFormContext();

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

  const [localeValidation, setLocaleValidation] = React.useState(
    attribute
      ? getLocaleValidation(
          attribute,
          locales,
          getAvailableLocales(),
          getScopeFormValue(),
          translate
        )
      : {}
  );
  const [scopeValidation, setScopeValidation] = React.useState(
    attribute ? getScopeValidation(attribute, scopes, translate) : {}
  );

  React.useEffect(() => {
    setLocaleValidation(
      attribute
        ? getLocaleValidation(
            attribute,
            locales,
            getAvailableLocales(),
            getScopeFormValue(),
            translate
          )
        : {}
    );
    setScopeValidation(
      attribute ? getScopeValidation(attribute, scopes, translate) : {}
    );
  }, [JSON.stringify(getAvailableLocales())]);

  useRegisterConst(`content.conditions[${lineNumber}].field`, condition.field);

  const handleScopeChange = () => {
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setValue(`content.conditions[${lineNumber}].locale`, undefined);
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
