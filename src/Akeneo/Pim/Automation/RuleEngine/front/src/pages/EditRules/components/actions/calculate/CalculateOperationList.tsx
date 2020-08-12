import React from 'react';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import {
  ConstantOperand,
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';
import { CalculateOperatorSelector } from './CalculateOperatorSelector';
import styled from 'styled-components';
import { InputNumber } from '../../../../../components/Inputs';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { AttributePropertiesSelector } from './AttributePropertiesSelector';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

type DropTarget = {
  operationLineNumber: number;
};

type OperationLineProps = {
  operand: Operand;
  operator?: Operator;
  locales: Locale[];
  scopes: IndexedScopes;
  operationLineNumber: number;
  dropTarget: DropTarget | null;
  setDropTarget: (dropTarget: DropTarget | null) => void;
  moveOperation: (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => void;
};

const OperationLine: React.FC<OperationLineProps> = ({
  operand,
  operator,
  locales,
  scopes,
  operationLineNumber,
  dropTarget,
  setDropTarget,
  moveOperation,
}) => {
  const translate = useTranslate();
  const operandValue = Object.keys(operand).includes('value')
    ? (operand as ConstantOperand)
    : null;
  const operandField = Object.keys(operand).includes('field')
    ? (operand as FieldOperand)
    : null;

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

  return (
    <li
      className={'AknRuleOperation-line'}
      draggable={true}
      onDragOver={onDragOver}
      onDragEnd={onDragEnd}
      data-line-number={operationLineNumber}>
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
              <CalculateOperatorSelector
                name={`edit-rules-action-operation-list-${operationLineNumber}-operator`}
                defaultValue={operator || Operator.ADD}
                hiddenLabel
              />
            </span>
          )}
          {operandValue && (
            <span className={'AknRuleOperation-element'}>
              <InputNumber
                className={
                  'AknTextField AknNumberField AknRuleOperation-inputValue'
                }
                data-testid={`edit-rules-action-operation-list-${operationLineNumber}-number`}
                hiddenLabel={true}
                defaultValue={operandValue.value}
                step={'any'}
              />
            </span>
          )}
          {operandField && (
            <AttributePropertiesSelector
              operationLineNumber={operationLineNumber}
              attributeCode={operandField.field}
              localeCode={operandField.locale}
              scopeCode={operandField.scope}
              currencyCode={operandField.currency}
              locales={locales}
              scopes={scopes}
            />
          )}
        </div>
      </div>
      <div className={'AknRuleOperation-remove'}>
        <DeleteButton type={'button'}>
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
  defaultSource: Operand;
  defaultOperationList: Operation[];
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  scopes: IndexedScopes;
};

const CalculateOperationList: React.FC<Props> = ({
  defaultSource,
  defaultOperationList,
  locales,
  scopes,
}) => {
  const [dropTarget, setDropTarget] = React.useState<DropTarget | null>(null);
  const [source, setSource] = React.useState<Operand>(defaultSource);
  const [operationList, setOperationList] = React.useState<Operation[]>(
    defaultOperationList
  );

  const moveOperation = (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => {
    const operations = [source, ...operationList];
    operations.splice(
      newOperationLineNumber,
      0,
      operations.splice(currentOperationLineNumber, 1)[0]
    );
    setSource(operations[0]);
    setOperationList(operations.slice(1) as Operation[]);
  };

  return (
    <ul className={'AknRuleOperation'}>
      <OperationLine
        operand={source}
        locales={locales}
        scopes={scopes}
        operationLineNumber={0}
        dropTarget={dropTarget}
        setDropTarget={setDropTarget}
        moveOperation={moveOperation}
      />
      {operationList.map((operation: Operation, key: number) => {
        const { operator, ...operand } = operation;

        return (
          <OperationLine
            key={key}
            operand={operand}
            operator={operator}
            locales={locales}
            scopes={scopes}
            operationLineNumber={key + 1}
            dropTarget={dropTarget}
            setDropTarget={setDropTarget}
            moveOperation={moveOperation}
          />
        );
      })}
    </ul>
  );
};

export { CalculateOperationList };
