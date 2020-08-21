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
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';

type SourceOrOperation = Operand | Operation;

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

const getConstantValidation = (isValue: boolean, operator: Operator, translate: Translate) => {
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
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  removeOperation: (operationLineNumber: number) => () => void;
  version: number;
  isValue: boolean;
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
  isValue,
}) => {
  const translate = useTranslate();
  const { setValue, watch, errors } = useFormContext();
  const { formName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const fieldOperand = sourceOrOperation as FieldOperand;

  React.useEffect(() => {
    // When lines are moved/removed, we have an issue with <Controller /> and selectors.
    // To reproduce that, change the value of the operator or scope or locale or currency, then move the line. The
    // default value comes back visually (even if the good value is well store in the react hook form state)
    // To fix the display we set again the values. The react hook form state is unchanged but the display is now good.
    // Maybe react hook form v6 handles better this use case.
    return;
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
          <Controller
            as={<input type="hidden"/>}
            name={formName(`${baseFormName}.operator`)}
            rules={operationLineNumber > 0 ? {
              required: translate(
                'pimee_catalog_rule.exceptions.required_operator_for_operation'
              ),
            }: {}}
          />
          {operationLineNumber > 0 && (
            <span
              className={`AknRuleOperation-element AknRuleOperation-elementOperator${
                isOperatorInError ? ' select2-container-error' : ''
              }`}>
              <CalculateOperatorSelector
                value={watch(formName(`${baseFormName}.operator`))}
                hiddenLabel
                onChange={(operator) => { setValue(formName(`${baseFormName}.operator`), operator)}}
                label={translate('pim_common.operator')}
              />
            </span>
          )}
          <Controller
            name={formName(`${baseFormName}.value`)}
            as={<input type="hidden"/>}
            rules={getConstantValidation(
              isValue,
              watch(formName(`${baseFormName}.operator`)),
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
                defaultValue={watch(formName(`${baseFormName}.value`))}
                step={'any'}
                onChange={(e) => { setValue(formName(`${baseFormName}.value`), e.target.value ? Number(e.target.value) : '') }}
              />
            </span>
          ) : (
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
