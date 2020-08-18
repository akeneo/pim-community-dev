import React from 'react';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import { useControlledFormInputAction } from '../../../hooks';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { Controller, useFormContext } from 'react-hook-form';
import { CalculateOperatorSelector } from './CalculateOperatorSelector';
import styled from 'styled-components';
import { Translate } from '../../../../../dependenciesTools';
import { AttributePropertiesSelector } from './AttributePropertiesSelector';
import { InputNumber } from '../../../../../components/Inputs';
import {
  ConstantOperand,
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';

type SourceOrOperation = Operand | Operation;

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

const getConstantValidation = (operator: Operator, translate: Translate) => {
  return {
    required: translate(
      'pimee_catalog_rule.exceptions.required_constant_for_operation'
    ),
    validate: (value: any) => {
      if ('0' === value && Operator.DIVIDE === operator) {
        return translate('pimee_catalog_rule.exceptions.division_by_zero');
      }
      return true;
    },
  };
};

type OperationLineProps = {
  baseFormName: string;
  sourceOrOperation: SourceOrOperation;
  locales: Locale[];
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  removeOperation: (operationLineNumber: number) => () => void;
  version: number;
};

const OperationLine: React.FC<OperationLineProps> = ({
  baseFormName,
  sourceOrOperation,
  locales,
  scopes,
  lineNumber,
  operationLineNumber,
  removeOperation,
  version,
}) => {
  const translate = useTranslate();
  const { setValue, watch, errors } = useFormContext();
  const { formName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const constantOperand = sourceOrOperation as ConstantOperand;
  const fieldOperand = sourceOrOperation as FieldOperand;

  React.useEffect(() => {
    // When lines are moved/removed, we have an issue with <Controller /> and selectors.
    // To reproduce that, change the value of the operator or scope or locale or currency, then move the line. The
    // default value comes back visually (even if the good value is well store in the react hook form state)
    // To fix the display we set again the values. The react hook form state is unchanged but the display is now good.
    // Maybe react hook form v6 handles better this use case.
    ['operator', 'locale', 'scope', 'currency'].forEach((key: string) => {
      setValue(
        formName(`${baseFormName}.${key}`),
        getFormValue(`${baseFormName}.${key}`) || undefined
      );
    });
  }, [version]);

  const isOperatorInError =
    typeof errors?.content?.actions?.[lineNumber]?.full_operation_list?.[
      operationLineNumber
    ]?.operator === 'object';
  const isValueInError =
    typeof errors?.content?.actions?.[lineNumber]?.full_operation_list?.[
      operationLineNumber
    ]?.value === 'object';

  return (
    <li
      className={`AknRuleOperation-line`}
      data-line-number={operationLineNumber}
      data-testid={`edit-rules-action-operation-list-${operationLineNumber}-item`}>
      <div className={'AknRuleOperation-details'}>
        <div className={'AknRuleOperation-detailsContainer'}>
          <span
            className={`AknRuleOperation-moveIcon`}
            role={'operation-item-move-handle'}
          />
          {operationLineNumber > 0 && (
            <span
              className={`AknRuleOperation-element AknRuleOperation-elementOperator${
                isOperatorInError ? ' select2-container-error' : ''
              }`}>
              <Controller
                as={CalculateOperatorSelector}
                name={formName(`${baseFormName}.operator`)}
                defaultValue={(sourceOrOperation as Operation).operator || null}
                value={(sourceOrOperation as Operation).operator || null}
                hiddenLabel
                rules={{
                  required: translate(
                    'pimee_catalog_rule.exceptions.required_operator_for_operation'
                  ),
                }}
              />
            </span>
          )}
          {constantOperand.value !== null &&
            typeof constantOperand.value !== 'undefined' && (
              <span className={'AknRuleOperation-element'}>
                <Controller
                  as={InputNumber}
                  name={formName(`${baseFormName}.value`)}
                  className={`AknTextField AknNumberField AknRuleOperation-inputValue${
                    isValueInError ? ' AknTextField--error' : ''
                  }`}
                  data-testid={`edit-rules-action-operation-list-${operationLineNumber}-number`}
                  hiddenLabel={true}
                  defaultValue={constantOperand.value}
                  step={'any'}
                  rules={getConstantValidation(
                    watch(formName(`${baseFormName}.operator`)),
                    translate
                  )}
                />
              </span>
            )}
          {fieldOperand.field && (
            <>
              <AttributePropertiesSelector
                operationLineNumber={operationLineNumber}
                attributeCode={fieldOperand.field}
                fieldFormName={formName(`${baseFormName}.field`)}
                localeFormName={formName(`${baseFormName}.locale`)}
                scopeFormName={formName(`${baseFormName}.scope`)}
                currencyFormName={formName(`${baseFormName}.currency`)}
                locales={locales}
                scopes={scopes}
                defaultLocale={fieldOperand.locale || undefined}
                defaultScope={fieldOperand.scope || undefined}
                defaultCurrency={fieldOperand.currency || undefined}
              />
            </>
          )}
        </div>
      </div>
      <div className={'AknRuleOperation-remove'}>
        <DeleteButton
          type={'button'}
          onClick={removeOperation(operationLineNumber)}
          data-testid={`edit-rules-action-operation-list-${operationLineNumber}-remove-button`}>
          <img
            alt={translate('pimee_catalog_rule.form.edit.conditions.delete')}
            src='/bundles/akeneopimruleengine/assets/icons/icon-delete-grey100.svg'
          />
        </DeleteButton>
      </div>
    </li>
  );
};

export { OperationLine };
