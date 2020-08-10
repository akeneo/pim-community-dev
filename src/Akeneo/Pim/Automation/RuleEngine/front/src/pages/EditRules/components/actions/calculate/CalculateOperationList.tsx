import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
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

// eslint-disable-next-line @typescript-eslint/no-var-requires
const uuidV4 = require('uuid/v4');

type SourceOrOperationIndexed = {
  id: string;
} & (Operand | Operation);

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

type DropTarget = {
  operationLineNumber: number;
};

const addUniqueIdToList = (sourceOrOperationList: (Operand | Operation)[] | undefined) => {
  if (!sourceOrOperationList) {
    return [];
  }

  return sourceOrOperationList.map((sourceOrOperation: Operand | Operation) => {
    return { id: uuidV4(), ...sourceOrOperation };
  });
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
  removeOperation: (operationLineNumber: number) => () => void;
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
  removeOperation,
}) => {
  const translate = useTranslate();
  const { formName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const operandField = getFormValue(`${baseFormName}.field`) || null;

  const { watch } = useFormContext();
  watch(formName(`${baseFormName}.operator`));

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
            onClick={removeOperation(operationLineNumber)}
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
  const { setValue } = useFormContext();
  const { formName, getFormValue } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const [dropTarget, setDropTarget] = React.useState<DropTarget | null>(null);
  const [sourceAndOperations, setSourceAndOperations] = React.useState<SourceOrOperationIndexed[]>(
    addUniqueIdToList([getFormValue('source'), ...getFormValue('operation_list')])
  );

  const getSourceFormValue = () => getFormValue('source');
  const getOperationListFormValue = () => getFormValue('operation_list');

  React.useEffect(() => {
    console.log('after render formValues = ', getFormValue('operation_list'));
  });

  const setSourceAndOperationValues = (
    sourceAndOperations: SourceOrOperationIndexed[]
  ) => {
    console.log('setSourceAndOperationValues', Array.from(sourceAndOperations));
    const [source, ...operationList] = sourceAndOperations;

    console.log('setSourceAndOperationValues source = ', source);
    console.log('setSourceAndOperationValues operationList = ', operationList);

    ['field', 'value', 'scope', 'locale', 'currency'].forEach((key: string) =>
      setValue(
        formName('source') + `.${key}`,
        source ? (source as any)[key] || undefined : undefined
      )
    );

    const operationFields = [
      'field',
      'value',
      'scope',
      'locale',
      'currency',
      'operator',
    ];
    getOperationListFormValue().map(
      (_operation: any, operationIndex: number) => {
        if ('undefined' === typeof operationList[operationIndex]) {
          console.log(
            'unregister',
            formName('operation_list') + `[${operationIndex}]`
          );
          operationFields.forEach((key: string) =>
            setValue(
              formName('operation_list') + `[${operationIndex}].${key}`,
              undefined
            )
          );
          setValue(
            formName('operation_list') + `[${operationIndex}]`,
            undefined
          );
          return;
        }
        operationFields.forEach((key: string) =>
          setValue(
            formName('operation_list') + `[${operationIndex}].${key}`,
            'undefined' !== typeof operationList[operationIndex]
              ? (operationList as any)[operationIndex][key] || undefined
              : undefined
          )
        );
      }
    );

    console.log('--> end setOperationListAndSource');
    console.log('getSourceFormValue', getSourceFormValue());
    console.log('getOperationListFormValue', getOperationListFormValue());
  };

  const removeOperation = (lineToRemove: number) => () => {
    console.log('removeOperation lineToRemove = ', lineToRemove);
    console.log('operationList', sourceAndOperations);

    const newSourceAndOperations = [
      ...sourceAndOperations.slice(0, lineToRemove),
      ...sourceAndOperations.slice(lineToRemove + 1),
    ];
    setSourceAndOperationValues(newSourceAndOperations);
    setSourceAndOperations(
      addUniqueIdToList(
        newSourceAndOperations.filter(
          (sourceOrOperation: any) => sourceOrOperation?.field || sourceOrOperation?.value
        )
      )
    );
  };

  const moveOperation = (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => {
    const operations = [...sourceAndOperations];
    operations.splice(
      newOperationLineNumber,
      0,
      operations.splice(currentOperationLineNumber, 1)[0]
    );
    setSourceAndOperationValues(operations);
    setSourceAndOperations(addUniqueIdToList(operations));
  };

  return (
    <ul className={'AknRuleOperation'}>
      {sourceAndOperations &&
        sourceAndOperations.map((sourceOrOperation: SourceOrOperationIndexed, operationLineNumber) => {
          const baseFormName = 0 === operationLineNumber
            ? 'source'
            : `operation_list[${operationLineNumber - 1}]`;

          return (
            <OperationLine
              key={sourceOrOperation.id}
              baseFormName={baseFormName}
              locales={locales}
              scopes={scopes}
              lineNumber={lineNumber}
              operationLineNumber={operationLineNumber}
              dropTarget={dropTarget}
              setDropTarget={setDropTarget}
              moveOperation={moveOperation}
              removeOperation={removeOperation}
            />
          );
        })}
    </ul>
  );
};

export { CalculateOperationList };
