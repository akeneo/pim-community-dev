import React from "react";
import { Controller, useFormContext } from 'react-hook-form';
import { ConditionLineProps } from "./ConditionLineProps";
import {
  FieldColumn, LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn
} from "./style";
import { OperatorSelector } from "../../../../components/Selectors/OperatorSelector";
import { CompletenessOperatorsCompatibility } from "../../../../models/conditions";
import { useControlledFormInputCondition } from "../../hooks";
import { useTranslate } from "../../../../dependenciesTools/hooks";
import { Operator } from "../../../../models/Operator";
import { InputNumber } from "../../../../components/Inputs";
import { LineErrors } from "../LineErrors";
import { ScopeSelector } from "../../../../components/Selectors/ScopeSelector";
import { LocaleSelector } from "../../../../components/Selectors/LocaleSelector";

const CompletenessConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const { errors } = useFormContext();

  const {
    fieldFormName,
    operatorFormName,
    getOperatorFormValue,
    valueFormName,
    getValueFormValue,
    getScopeFormValue,
    localeFormName,
    getLocaleFormValue,
    scopeFormName,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  /**
   * The Completeness filter have deprecated Operators. These former operator still works and have the same behavior
   * than the new ones. At the loading of the condition line, to avoid a hard replacement with the new one (and it
   * will throw a "there is unsaved changes"), we consider to save it like this if the user does not change.
   * When the user will change the operator, he can only use the new ones.
   * @see src/Akeneo/Pim/Enrichment/Bundle/Elasticsearch/Filter/Field/Product/CompletenessFilter.php
   */
  const getCompatibleOperator: (operator: Operator) => Operator = (operator) => {
    return CompletenessOperatorsCompatibility.has(operator) ? CompletenessOperatorsCompatibility.get(operator) as Operator : operator;
  }

  return <div className={'AknGrid-bodyCell'}>
    <Controller
      as={<input type='hidden' />}
      name={fieldFormName}
      defaultValue='completeness'
    />
    <FieldColumn
      className={'AknGrid-bodyCell--highlight'}
      title={translate('pim_common.completeness')}>
      {translate('pim_common.completeness')}
    </FieldColumn>
    <OperatorColumn>
      <Controller
        as={OperatorSelector}
        availableOperators={Array.from(CompletenessOperatorsCompatibility.values())}
        data-testid={`edit-rules-input-${lineNumber}-operator`}
        hiddenLabel
        name={operatorFormName}
        defaultValue={getCompatibleOperator(getOperatorFormValue() ?? Operator.EQUALS)}
        value={getCompatibleOperator(getOperatorFormValue())}
      />
    </OperatorColumn>
    <ValueColumn small>
      <Controller
        as={InputNumber}
        className={`AknTextField${isElementInError('value') ? ' AknTextField--error' : ''}`}
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={valueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
        defaultValue={getValueFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required_value'),
        }}
      />
    </ValueColumn>
    <ScopeColumn
      className={
        isElementInError('scope') ? 'select2-container-error' : ''
      }>
      <Controller
        allowClear={false}
        as={ScopeSelector}
        availableScopes={Object.values(scopes)}
        currentCatalogLocale={currentCatalogLocale}
        data-testid={`edit-rules-input-${lineNumber}-scope`}
        hiddenLabel
        name={scopeFormName}
        defaultValue={getScopeFormValue()}
        value={getScopeFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required_scope_completeness'),
        }}
      />
    </ScopeColumn>
    <LocaleColumn
      className={
        isElementInError('locale') ? 'select2-container-error' : ''
      }>
      <Controller
        as={LocaleSelector}
        data-testid={`edit-rules-input-${lineNumber}-locale`}
        hiddenLabel
        availableLocales={locales}
        defaultValue={getLocaleFormValue()}
        value={getLocaleFormValue()}
        allowClear={false}
        name={localeFormName}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required_locale_completeness'),
        }}
      />
    </LocaleColumn>
    <LineErrors lineNumber={lineNumber} type='conditions' />

  </div>
}

export { CompletenessConditionLine }
