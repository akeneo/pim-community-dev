import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { StatusOperators } from '../../../../models/conditions';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import { FieldColumn, OperatorColumn, ValueColumn } from './style';
import { StatusSelector } from '../../../../components/Selectors/StatusSelector';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';

import { useControlledFormInputCondition } from '../../hooks';

const INIT_OPERATOR = Operator.EQUALS;

const StatusConditionLine: React.FC<ConditionLineProps> = ({ lineNumber }) => {
  const translate = useTranslate();
  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputCondition<boolean>(lineNumber);

  return (
    <div className={'AknGrid-bodyCell'}>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='enabled'
      />
      <FieldColumn
        className={'AknGrid-bodyCell--highlight'}
        title={translate('pimee_catalog_rule.form.edit.fields.enabled')}>
        {translate('pimee_catalog_rule.form.edit.fields.enabled')}
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
      <ValueColumn>
        <Controller
          name={valueFormName}
          as={<span hidden />}
          defaultValue={getValueFormValue()}
          rules={{
            validate: value => {
              return (
                typeof value === 'boolean' ||
                translate('pimee_catalog_rule.exceptions.required_value')
              );
            },
          }}
        />
        <StatusSelector
          id={`edit-rules-input-${lineNumber}-value`}
          name={valueFormName}
          label={`${translate('pim_common.status')} ${translate(
            'pim_common.required_label'
          )}`}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.set_status.placeholder'
          )}
          hiddenLabel={true}
          value={getValueFormValue()}
          onChange={setValueFormValue}
        />
      </ValueColumn>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </div>
  );
};

export { StatusConditionLine };
