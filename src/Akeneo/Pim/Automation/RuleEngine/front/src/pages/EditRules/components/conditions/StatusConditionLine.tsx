import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { StatusOperators } from '../../../../models/conditions';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import {
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { StatusSelector } from '../../../../components/Selectors/StatusSelector';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';

import { useControlledFormInputCondition } from '../../hooks';

const INIT_OPERATOR = Operator.EQUALS;

const StatusConditionLine: React.FC<ConditionLineProps> = ({ lineNumber }) => {
  const translate = useTranslate();
  const { errors } = useFormContext();
  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
  } = useControlledFormInputCondition<boolean>(lineNumber);

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='enabled'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pim_common.status')}>
          {translate('pim_common.status')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={StatusOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? INIT_OPERATOR}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        <ValueColumn
          className={
            isElementInError('value') ? 'select2-container-error' : ''
          }>
          <Controller
            name={valueFormName}
            as={StatusSelector}
            defaultValue={getValueFormValue()}
            rules={{
              validate: value => {
                return (
                  typeof value === 'boolean' ||
                  translate('pimee_catalog_rule.exceptions.required')
                );
              },
            }}
            id={`edit-rules-input-${lineNumber}-value`}
            label={`${translate('pim_common.status')} ${translate(
              'pim_common.required_label'
            )}`}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.set_status.placeholder'
            )}
            hiddenLabel={true}
            value={getValueFormValue()}
          />
        </ValueColumn>
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { StatusConditionLine };
