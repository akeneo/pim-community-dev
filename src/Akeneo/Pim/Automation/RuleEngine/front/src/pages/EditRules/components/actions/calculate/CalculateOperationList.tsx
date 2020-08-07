import React from 'react';
import { Controller } from 'react-hook-form';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import { Operand } from '../../../../../models/actions/Calculate/Operand';
import { CalculateOperatorSelector } from './CalculateOperatorSelector';
import styled from 'styled-components';
import { InputNumber } from '../../../../../components/Inputs';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { AttributePropertiesSelector } from './AttributePropertiesSelector';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { useControlledFormInputAction } from '../../../hooks';

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
};

const OperationLine: React.FC<OperationLineProps> = ({
  baseFormName,
  locales,
  scopes,
  lineNumber,
  operationLineNumber,
  dropTarget,
  setDropTarget,
  moveOperation,
}) => {
  const translate = useTranslate();
  const {
    formName,
    getFormValue,
    // isFormFieldInError,
  } = useControlledFormInputAction<string | null>(lineNumber);
  const operandField = getFormValue(`${baseFormName}.field`) || null;

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
              <Controller
                as={CalculateOperatorSelector}
                name={formName(`${baseFormName}.operator`)}
                value={getFormValue(`${baseFormName}.operator`) || Operator.ADD}
                hiddenLabel
              />
            </span>
          )}
          {!operandField && (
            <span className={'AknRuleOperation-element'}>
              <Controller
                as={InputNumber}
                name={formName(`${baseFormName}.value`)}
                className={
                  'AknTextField AknNumberField AknRuleOperation-inputValue'
                }
                data-testid={`edit-rules-action-operation-list-${operationLineNumber}-number`}
                hiddenLabel={true}
                defaultValue={getFormValue(`${baseFormName}.value`)}
                step={'any'}
              />
            </span>
          )}
          {operandField && (
            <>
              <Controller
                as={<input type='hidden' />}
                name={formName(`${baseFormName}.field`)}
                defaultValue={getFormValue(`${baseFormName}.field`)}
              />
              <AttributePropertiesSelector
                lineNumber={lineNumber}
                operationLineNumber={operationLineNumber}
                attributeCode={getFormValue(`${baseFormName}.field`)}
                localeFormName={formName(`${baseFormName}.locale`)}
                scopeFormName={formName(`${baseFormName}.scope`)}
                currencyFormName={formName(`${baseFormName}.currency`)}
                locales={locales}
                scopes={scopes}
              />
            </>
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
  lineNumber: number;
  defaultSource: Operand;
  defaultOperationList: Operation[];
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  scopes: IndexedScopes;
};

const CalculateOperationList: React.FC<Props> = ({
  lineNumber,
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
        baseFormName={'source'}
        locales={locales}
        scopes={scopes}
        lineNumber={lineNumber}
        operationLineNumber={0}
        dropTarget={dropTarget}
        setDropTarget={setDropTarget}
        moveOperation={moveOperation}
      />
      {operationList.map((_operation: Operation, key: number) => {
        return (
          <OperationLine
            key={key}
            baseFormName={`operation_list[${key}]`}
            locales={locales}
            scopes={scopes}
            lineNumber={lineNumber}
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
