import React from 'react';
import { useFormContext, ErrorMessage } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/TextAttributeCondition';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from './ConditionLineProps';
import { Locale, LocaleCode, ScopeCode } from '../../../../models';
import { InputText } from '../../../../components/Inputs';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { InputErrorMsg } from '../../../../components/InputErrorMsg';
import {
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
} from './style';

type TextAttributeConditionLineProps = ConditionLineProps & {
  condition: TextAttributeCondition;
};

const TextAttributeConditionLine: React.FC<TextAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const {
    register,
    watch,
    setValue,
    errors,
    triggerValidation,
  } = useFormContext();

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

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const localeValidation: any = {};
  if (condition.attribute.localizable) {
    localeValidation['required'] = translate('pimee_catalog_rule.exceptions.required');
  }
  localeValidation['validate'] = (localeCode: any) => {
    if (condition.attribute.localizable) {
      console.log(locales);
      if (!locales.some(locale => locale.code === localeCode)) {
        return `Unknown locale or non activated ${localeCode}`;
      }
      if (!getAvailableLocales().some(locale => locale.code === localeCode)) {
        return 'Need to be bounded';
      }
    } else {
      if (localeCode) {
        return `Attribute is not localizable, please drop the locale`;
      }
    }
    return true;
  }

  const scopeValidation: any = {};
  if (condition.attribute.scopable) {
    scopeValidation['required'] = translate('pimee_catalog_rule.exceptions.required');
  }
  scopeValidation['validate'] = (scopeCode: any) => {
    if (condition.attribute.scopable) {
      if (!scopes[scopeCode]) {
        return `Unknown scope ${scopeCode}`;
      }
    } else {
      if (scopeCode) {
        return `Attribute is not scopable, please drop the scope`;
      }
    }
    return true;
  }

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    {
      field: condition.field,
      operator: condition.operator,
      value: condition.value,
      scope: condition.scope,
      locale: condition.locale,
    },
    {
      scope: scopeValidation,
      locale: localeValidation,
    },
    [condition]
  );

  const setValueFormValue = (value: string | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);
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
    if (!shouldDisplayValue()) {
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
          availableOperators={TextAttributeOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      <ValueColumn>
        {shouldDisplayValue() && (
          <InputText
            data-testid={`edit-rules-input-${lineNumber}-value`}
            name={`content.conditions[${lineNumber}].value`}
            label={translate('pim_common.code')}
            ref={register}
            hiddenLabel={true}
          />
        )}
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
          >
            <ErrorMessage
              errors={errors}
              name={`content.conditions[${lineNumber}].scope`}>
              {({ message }) => <InputErrorMsg>{message}</InputErrorMsg>}
            </ErrorMessage>
          </ScopeSelector>
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
          >
            <ErrorMessage
              errors={errors}
              name={`content.conditions[${lineNumber}].locale`}>
              {({ message }) => <InputErrorMsg>{message}</InputErrorMsg>}
            </ErrorMessage>
          </LocaleSelector>
        )}
      </LocaleColumn>
    </div>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
