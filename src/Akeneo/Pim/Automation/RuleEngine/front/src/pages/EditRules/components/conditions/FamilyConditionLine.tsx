import React from 'react';
import styled from 'styled-components';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  FamilyCondition,
  FamilyOperators,
} from '../../../../models/FamilyCondition';
import { Operator } from '../../../../models/Operator';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';

const FieldColumn = styled.span`
  width: 100px;
  display: inline-block;
  padding: 0 2px;
  overflow: hidden;
`;

const OperatorColumn = styled.span`
  width: 150px;
  display: inline-block;
  padding: 0 2px;
`;

const ValueColumn = styled.span`
  width: 400px;
  display: inline-block;
  padding: 0 2px;
`;

type FamilyConditionLineProps = ConditionLineProps & {
  condition: FamilyCondition;
};

const FamilyConditionLine: React.FC<FamilyConditionLineProps> = ({
  router,
  lineNumber,
  translate,
  currentCatalogLocale,
}) => {
  const { register, setValue, getValues } = useFormContext();

  register({ name: `content.conditions[${lineNumber}].field` });
  register({ name: `content.conditions[${lineNumber}].operator` });
  register({ name: `content.conditions[${lineNumber}].value` });

  const getFormOperator = (): Operator =>
    getValues()[`content.conditions[${lineNumber}].operator`];
  const getFormValue = (): string[] | null =>
    getValues()[`content.conditions[${lineNumber}].value`];

  const valueMustBeSet = (): boolean => {
    return ![Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(
      getFormOperator()
    );
  };

  const setFormValue = (newValue: string[] | null): void => {
    setValue(
      `content.conditions[${lineNumber}].value`,
      valueMustBeSet() ? newValue || [] : null
    );
  };
  const setFormOperator = (operator: Operator): void => {
    setValue(`content.conditions[${lineNumber}].operator`, operator);
    setFormValue(getFormValue());
  };

  const [displayFamilySelector, setDisplayFamilySelector] = React.useState<
    boolean
  >(valueMustBeSet());

  return (
    <div>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {translate('pimee_catalog_rule.form.edit.fields.family')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          currentOperator={getFormOperator()}
          availableOperators={FamilyOperators}
          translate={translate}
          onSelectorChange={(value: string): void => {
            setFormOperator(value as Operator);
            setDisplayFamilySelector(valueMustBeSet());
          }}
        />
      </OperatorColumn>
      {displayFamilySelector && (
        <ValueColumn>
          <FamilySelector
            router={router}
            id={`edit-rules-input-${lineNumber}-value`}
            label='Families'
            hiddenLabel={true}
            multiple={true}
            selectedFamilyCodes={getFormValue() || []}
            currentCatalogLocale={currentCatalogLocale}
            onSelectorChange={(values: string[]): void => {
              setFormValue(values);
            }}
          />
        </ValueColumn>
      )}
    </div>
  );
};

export { FamilyConditionLine, FamilyConditionLineProps };
