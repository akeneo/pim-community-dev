import React from 'react';
import styled from 'styled-components';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  FamilyCondition,
  FamilyOperators,
} from '../../../../models/FamilyCondition';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { Operator } from '../../../../models/Operator';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';

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
  condition,
}) => {
  const { watch } = useFormContext();

  useValueInitialization(`content.conditions[${lineNumber}]`, {
    field: condition.field,
    operator: condition.operator,
    value: condition.value,
  });

  const getFormOperator = (): Operator =>
    watch(`content.conditions[${lineNumber}].operator`);

  const shouldDisplayFamilySelector = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getFormOperator()
    );
  };

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
          availableOperators={FamilyOperators}
          translate={translate}
          name={`content.conditions[${lineNumber}].operator`}
        />
      </OperatorColumn>
      {shouldDisplayFamilySelector() && (
        <ValueColumn>
          <FamilySelector
            router={router}
            id={`edit-rules-input-${lineNumber}-value`}
            label='Families'
            hiddenLabel={true}
            multiple={true}
            currentCatalogLocale={currentCatalogLocale}
            name={`content.conditions[${lineNumber}].value`}
          />
        </ValueColumn>
      )}
    </div>
  );
};

export { FamilyConditionLine, FamilyConditionLineProps };
