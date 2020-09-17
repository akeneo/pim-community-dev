import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { EntityType, EntityTypeOperators } from '../../../../models/conditions';
import { EntityTypeSelector } from '../../../../components/Selectors/EntityTypeSelector';

const EntityTypeConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
}) => {
  const translate = useTranslate();
  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<EntityType>(lineNumber);

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='entity_type'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate(
            'pimee_catalog_rule.form.edit.fields.entity_type.label'
          )}>
          {translate('pimee_catalog_rule.form.edit.fields.entity_type.label')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            availableOperators={EntityTypeOperators}
            name={operatorFormName}
            value={Operator.EQUALS}
            defaultValue={Operator.EQUALS}
            hiddenLabel
            disabled
          />
        </OperatorColumn>
        <ValueColumn
          className={
            isFormFieldInError('value') ? 'select2-container-error' : ''
          }>
          <Controller
            name={valueFormName}
            as={EntityTypeSelector}
            defaultValue={getValueFormValue()}
            rules={{
              required: translate('pimee_catalog_rule.exceptions.required'),
            }}
            id={`edit-rules-input-${lineNumber}-value`}
            label={`${translate(
              'pimee_catalog_rule.form.edit.fields.entity_type.label'
            )} ${translate('pim_common.required_label')}`}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.fields.entity_type.placeholder'
            )}
            hiddenLabel={true}
            value={getValueFormValue()}
          />
        </ValueColumn>
      </ConditionLineFormContainer>
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export { EntityTypeConditionLine };
