import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  FamilyCondition,
  FamilyOperators,
} from '../../../../models/FamilyCondition';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { Operator } from '../../../../models/Operator';
import { FamilyCode } from '../../../../models';
import { FieldColumn, OperatorColumn, ValueColumn } from './style';
import { FamiliesSelector } from "../../../../components/Selectors/FamiliesSelector";

type FamilyConditionLineProps = ConditionLineProps & {
  condition: FamilyCondition;
};

const FamilyConditionLine: React.FC<FamilyConditionLineProps> = ({
  router,
  lineNumber,
  translate,
  currentCatalogLocale,
  condition,
}) => {
  const { watch, setValue } = useFormContext();

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    {
      field: condition.field,
      operator: condition.operator,
      value: condition.value,
    },
    {},
    [condition]
  );

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);
  const getValueFormValue: () => FamilyCode[] = () =>
    watch(`content.conditions[${lineNumber}].value`);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const setValueFormValue = (value: FamilyCode[] | null) => {
    setValue(`content.conditions[${lineNumber}].value`, value);
  };
  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue()) {
      setValueFormValue(null);
    }
  };

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn
        className={'AknGrid-bodyCell--highlight'}
        title={translate('pimee_catalog_rule.form.edit.fields.family')}>
        {translate('pimee_catalog_rule.form.edit.fields.family')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          availableOperators={FamilyOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      {shouldDisplayValue() && (
        <ValueColumn>
          <FamiliesSelector
            router={router}
            id={`edit-rules-input-${lineNumber}-value`}
            label='Families'
            hiddenLabel={true}
            currentCatalogLocale={currentCatalogLocale}
            value={getValueFormValue()}
            onChange={setValueFormValue}
          />
        </ValueColumn>
      )}
    </div>
  );
};

export { FamilyConditionLine, FamilyConditionLineProps };
