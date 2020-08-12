import React from 'react';
import { Controller, useFormContext, useFieldArray } from 'react-hook-form';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import { CalculateOperatorSelector } from './CalculateOperatorSelector';
import styled from 'styled-components';
import { InputNumber } from '../../../../../components/Inputs';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { AttributePropertiesSelector } from './AttributePropertiesSelector';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { useControlledFormInputAction } from '../../../hooks';
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

type DropTarget = {
  operationLineNumber: number;
};

type OperationLineProps = {
  baseFormName: string;
  sourceOrOperation: SourceOrOperation;
  locales: Locale[];
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  dropTarget: DropTarget | null;
  setDropTarget: (dropTarget: DropTarget | null) => void;
  moveOperation: (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => void;
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
  dropTarget,
  setDropTarget,
  moveOperation,
  removeOperation,
  version,
}) => {
  const translate = useTranslate();
  const { setValue } = useFormContext();
  const { formName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const constantOperand = sourceOrOperation as ConstantOperand;
  const fieldOperand = sourceOrOperation as FieldOperand;

  const onDragOver = () => {
    setDropTarget({ operationLineNumber });
  };
  const onDragEnd = () => {
    if (null === dropTarget) {
      return;
    }

    if (dropTarget.operationLineNumber === operationLineNumber) {
      return;
    }

    moveOperation(operationLineNumber, dropTarget.operationLineNumber);
    setDropTarget(null);
  };

  React.useEffect(() => {
    // When lines are moved/removed, we have an issue with <Controller /> and selectors: the value of the old line
    // is displayed not the good value. In the state of react hook form the values are good.
    // To fix the display we set again the values. The react hook form state is unchanged but the display is now good.
    // Maybe react hook form v6 handles better this use case.
    ['operator', 'locale', 'scope', 'currency'].forEach((key: string) => {
      setValue(
        formName(`${baseFormName}.${key}`),
        getFormValue(`${baseFormName}.${key}`) || undefined
      );
    });
  }, [version]);

  return (
    <li
      className={'AknRuleOperation-line'}
      draggable={true}
      onDragOver={onDragOver}
      onDragEnd={onDragEnd}
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
              className={
                'AknRuleOperation-element AknRuleOperation-elementOperator'
              }>
              <Controller
                as={CalculateOperatorSelector}
                name={formName(`${baseFormName}.operator`)}
                defaultValue={
                  (sourceOrOperation as Operation).operator || Operator.ADD
                }
                value={
                  (sourceOrOperation as Operation).operator || Operator.ADD
                }
                hiddenLabel
              />
            </span>
          )}
          {constantOperand.value && (
            <span className={'AknRuleOperation-element'}>
              <Controller
                as={InputNumber}
                name={formName(`${baseFormName}.value`)}
                className={
                  'AknTextField AknNumberField AknRuleOperation-inputValue'
                }
                data-testid={`edit-rules-action-operation-list-${operationLineNumber}-number`}
                hiddenLabel={true}
                defaultValue={constantOperand.value}
                step={'any'}
              />
            </span>
          )}
          {fieldOperand.field && (
            <>
              <Controller
                as={<input type='hidden' />}
                name={formName(`${baseFormName}.field`)}
                defaultValue={fieldOperand.field}
              />
              <AttributePropertiesSelector
                operationLineNumber={operationLineNumber}
                attributeCode={fieldOperand.field}
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

type Props = {
  lineNumber: number;
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  scopes: IndexedScopes;
};

const CalculateOperationList: React.FC<Props> = ({
  lineNumber,
  locales,
  scopes,
}) => {
  const { formName } = useControlledFormInputAction<string | null>(lineNumber);
  const [dropTarget, setDropTarget] = React.useState<DropTarget | null>(null);
  const [version, setVersion] = React.useState<number>(1);

  const { fields, remove, move } = useFieldArray({
    name: formName('full_operation_list'),
  });

  const removeOperation = (lineToRemove: number) => () => {
    remove(lineToRemove);
    setVersion(version + 1);
  };

  const moveOperation = (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => {
    if (currentOperationLineNumber === newOperationLineNumber) {
      return;
    }

    move(currentOperationLineNumber, newOperationLineNumber);
    setVersion(version + 1);
  };

  return (
    <ul className={'AknRuleOperation'}>
      {fields &&
        fields.map((sourceOrOperation: any, operationLineNumber) => {
          return (
            <OperationLine
              key={sourceOrOperation.id}
              baseFormName={`full_operation_list[${operationLineNumber}]`}
              sourceOrOperation={sourceOrOperation}
              locales={locales}
              scopes={scopes}
              lineNumber={lineNumber}
              operationLineNumber={operationLineNumber}
              dropTarget={dropTarget}
              setDropTarget={setDropTarget}
              moveOperation={moveOperation}
              removeOperation={removeOperation}
              version={version}
            />
          );
        })}
    </ul>
  );
};

export { CalculateOperationList };
