import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { ParentOperators } from '../../../../models/conditions';
import { Operator } from '../../../../models/Operator';
import { IdentifiersSelector } from '../../../../components/Selectors/IdentifiersSelector';

const INIT_OPERATOR = Operator.IN_LIST;

const ParentConditionLine: React.FC<ConditionLineProps> = ({ lineNumber }) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    operatorFormName,
    getOperatorFormValue,
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='parent'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pimee_catalog_rule.form.edit.fields.parent')}>
          {translate('pimee_catalog_rule.form.edit.fields.parent')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={ParentOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? INIT_OPERATOR}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        {shouldDisplayValue() && (
          <ValueColumn
            className={
              isFormFieldInError('value') ? 'select2-container-error' : ''
            }>
            <Controller
              as={IdentifiersSelector}
              id={`edit-rules-input-${lineNumber}-value`}
              defaultValue={getValueFormValue()}
              hiddenLabel
              entityType='product_model'
              name={valueFormName}
              rules={{
                validate: (identifiers: any) =>
                  Array.isArray(identifiers) && identifiers.length === 0
                    ? translate('pimee_catalog_rule.exceptions.required')
                    : true,
                required: translate('pimee_catalog_rule.exceptions.required'),
              }}
              value={getValueFormValue()}
            />
          </ValueColumn>
        )}
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { ParentConditionLine };
