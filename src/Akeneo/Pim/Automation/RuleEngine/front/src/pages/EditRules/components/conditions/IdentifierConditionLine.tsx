import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import {
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { LineErrors } from '../LineErrors';
import { useTranslate } from '../../../../dependenciesTools/hooks';

import { useControlledFormInputCondition } from '../../hooks';
import { IdentifierOperators } from '../../../../models/conditions';
import {
  Identifier,
  IdentifiersSelector,
} from '../../../../components/Selectors/IdentifiersSelector';
import { InputText } from '../../../../components/Inputs';

const IdentifierConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string | string[]>(lineNumber);

  const shouldDisplaySelector: (operator: Operator) => boolean = operator => {
    return ([Operator.IN_LIST, Operator.NOT_IN_LIST] as Operator[]).includes(
      operator
    );
  };

  const [displaySelector, setDisplaySelector] = React.useState(
    shouldDisplaySelector(getOperatorFormValue())
  );

  React.useEffect(() => {
    const operatorFormValue = getOperatorFormValue();
    if (displaySelector && !shouldDisplaySelector(operatorFormValue)) {
      setValueFormValue('');
      setDisplaySelector(false);
    } else if (!displaySelector && shouldDisplaySelector(operatorFormValue)) {
      setValueFormValue([]);
      setDisplaySelector(true);
    }
  }, [getOperatorFormValue()]);

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='identifier'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pimee_catalog_rule.form.edit.fields.identifier')}>
          {translate('pimee_catalog_rule.form.edit.fields.identifier')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={IdentifierOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? IdentifierOperators[0]}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        <ValueColumn
          className={
            isFormFieldInError('value') ? 'select2-container-error' : ''
          }>
          <Controller
            as={<input type='hidden' />}
            name={valueFormName}
            defaultValue={getValueFormValue()}
            rules={{
              required: translate('pimee_catalog_rule.exceptions.required'),
              validate: (value: any) =>
                displaySelector && Array.isArray(value) && value.length === 0
                  ? translate('pimee_catalog_rule.exceptions.required')
                  : true,
            }}
          />
          {displaySelector ? (
            <IdentifiersSelector
              id={`edit-rules-input-${lineNumber}-value-selector`}
              data-testid={`edit-rules-input-${lineNumber}-value-selector`}
              name={valueFormName}
              label={translate('pimee_catalog_rule.rule.value')}
              hiddenLabel
              value={getValueFormValue() as Identifier[]}
              onChange={setValueFormValue}
            />
          ) : (
            <InputText
              className={
                isFormFieldInError('value')
                  ? 'AknTextField AknTextField--error'
                  : undefined
              }
              id={`edit-rules-input-${lineNumber}-value-text`}
              data-testid={`edit-rules-input-${lineNumber}-value-text`}
              name={valueFormName}
              label={translate('pimee_catalog_rule.rule.value')}
              hiddenLabel
              value={getValueFormValue() as string}
              onChange={event => setValueFormValue(event.target.value)}
            />
          )}
        </ValueColumn>
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { IdentifierConditionLine };
