import React from 'react';
import {Locale} from '../../../../../models';
import {IndexedScopes} from '../../../../../repositories/ScopeRepository';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import {useControlledFormInputAction} from '../../../hooks';
import {useTranslate} from '../../../../../dependenciesTools/hooks';
import {Controller, useFormContext} from 'react-hook-form';
import {CalculateOperatorSelector} from './CalculateOperatorSelector';
import styled from 'styled-components';
import {Translate} from '../../../../../dependenciesTools';
import {AttributePropertiesSelector} from '../attribute/AttributePropertiesSelector';
import {InputNumber} from '../../../../../components/Inputs';
import {
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';

type SourceOrOperation = Operand | Operation;

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

const getConstantValidation = (
  isValue: boolean,
  operator: Operator,
  translate: Translate
) => {
  if (!isValue) {
    return {};
  }

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
  uiLocales: Locale[];
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  removeOperation: (operationLineNumber: number) => () => void;
  isValue: boolean;
};

const CalculateOperationLine: React.FC<OperationLineProps> = ({
  baseFormName,
  sourceOrOperation,
  locales,
  uiLocales,
  scopes,
  lineNumber,
  operationLineNumber,
  removeOperation,
  isValue,
}) => {
  const translate = useTranslate();
  const {setValue, watch, errors} = useFormContext();
  const {formName, getFormValue} = useControlledFormInputAction<string | null>(
    lineNumber
  );
  const fieldOperand = sourceOrOperation as FieldOperand;

  const isOperatorInError =
    typeof errors?.content?.actions?.[lineNumber]?.full_operation_list?.[
      operationLineNumber
    ]?.operator === 'object';
  const isValueInError =
    typeof errors?.content?.actions?.[lineNumber]?.full_operation_list?.[
      operationLineNumber
    ]?.value === 'object';

  const getOperator = () =>
    getFormValue(formName(`${baseFormName}.operator`)) ??
    (sourceOrOperation as any).operator;
  watch(formName(`${baseFormName}.operator`));

  const getValue = () =>
    getFormValue(formName(`${baseFormName}.value`)) ??
    (sourceOrOperation as any).value;
  watch(formName(`${baseFormName}.value`));

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
          <Controller
            as={<input type='hidden' />}
            name={formName(`${baseFormName}.operator`)}
            rules={
              operationLineNumber > 0
                ? {
                    required: translate(
                      'pimee_catalog_rule.exceptions.required_operator_for_operation'
                    ),
                  }
                : {}
            }
          />
          {operationLineNumber > 0 && (
            <span
              className={`AknRuleOperation-element AknRuleOperation-elementOperator${
                isOperatorInError ? ' select2-container-error' : ''
              }`}>
              <CalculateOperatorSelector
                value={getOperator()}
                hiddenLabel
                onChange={operator => {
                  setValue(formName(`${baseFormName}.operator`), operator);
                }}
                label={translate('pim_common.operator')}
              />
            </span>
          )}
          <Controller
            name={formName(`${baseFormName}.value`)}
            as={<input type='hidden' />}
            rules={getConstantValidation(
              isValue,
              getFormValue(formName(`${baseFormName}.operator`)),
              translate
            )}
          />
          {isValue ? (
            <span className={'AknRuleOperation-element'}>
              <InputNumber
                className={`AknTextField AknNumberField AknRuleOperation-inputValue${
                  isValueInError ? ' AknTextField--error' : ''
                }`}
                data-testid={`edit-rules-action-operation-list-${operationLineNumber}-number`}
                hiddenLabel={true}
                defaultValue={getValue()}
                step={'any'}
                onChange={e => {
                  setValue(
                    formName(`${baseFormName}.value`),
                    e.target.value ? Number(e.target.value) : ''
                  );
                }}
              />
            </span>
          ) : (
            <>
              <AttributePropertiesSelector
                baseFormName={formName(baseFormName)}
                operationLineNumber={operationLineNumber}
                attributeCode={fieldOperand.field}
                locales={locales}
                scopes={scopes}
                isCurrencyRequired={true}
                uiLocales={uiLocales}
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

export {CalculateOperationLine};
