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
    console.log('--> useEffect operationLineNumber = ', operationLineNumber);
    // When lines are moved/removed, we have an issue with <Controller /> and selectors: the value of the old line
    // is displayed not the good value. In the state of react hook form the values are good.
    // To fix the display we set again the values. The react hook form state is unchanged but the display is now good.
    console.log(
      '----> key = ',
      'field = ',
      getFormValue(`${baseFormName}.field`) || undefined
    );
    console.log(
      '----> key = ',
      'value = ',
      getFormValue(`${baseFormName}.value`) || undefined
    );
    ['operator', 'locale', 'scope', 'currency'].forEach((key: string) => {
      console.log(
        '----> key = ',
        key,
        ' = ',
        getFormValue(`${baseFormName}.${key}`) || undefined
      );
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
  const [toInsert, setToInsert] = React.useState<
    { index: number; object: any } | undefined
  >();
  const [version, setVersion] = React.useState<number>(1);
  const [sourceKey, setSourceKey] = React.useState<string>('initial');

  const getSourceFormValue = () => getFormValue('source');

  const { fields, remove, move, insert } = useFieldArray({
    name: formName('operation_list'),
  });

  const removeOperation = (lineToRemove: number) => () => {
    if (0 === lineToRemove) {
      ['field', 'value', 'scope', 'locale', 'currency'].forEach(key => {
        setValue(
          formName(`source.${key}`),
          getFormValue(`operation_list[0].${key}`)
        );
      });
      setSourceKey(fields[0].id || 'initial');
      remove(0);
      setVersion(version + 1);
      return;
    }

    remove(lineToRemove - 1);
    setVersion(version + 1);
  };

  const moveOperation = (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => {
    if (currentOperationLineNumber === newOperationLineNumber) {
      return;
    }

    if (0 === currentOperationLineNumber) {
      const currentSource = { ...getSourceFormValue() };
      ['field', 'value', 'scope', 'locale', 'currency'].forEach(key => {
        setValue(
          formName(`source.${key}`),
          getFormValue(`operation_list[0].${key}`)
        );
      });
      setSourceKey(fields[0].id || 'initial');
      remove(0);

      // setToInsert for later (useEffect) because we cannot make 2 useFieldArray operations in a same render (see docs)
      setToInsert({
        index: newOperationLineNumber - 1,
        object: { operator: Operator.ADD, ...currentSource },
      });
      return;
    }

    if (0 === newOperationLineNumber) {
      const currentSource = { ...getSourceFormValue() };
      ['field', 'value', 'scope', 'locale', 'currency'].forEach(key => {
        setValue(
          formName(`source.${key}`),
          // operationToMove ? operationToMove[key] || undefined : undefined
          getFormValue(
            `operation_list[${currentOperationLineNumber - 1}].${key}`
          )
        );
      });
      setSourceKey(fields[currentOperationLineNumber - 1].id || 'initial');
      remove(currentOperationLineNumber - 1);

      // setToInsert for later (useEffect) because we cannot make 2 useFieldArray operations in a same render (see docs)
      setToInsert({
        index: 0,
        object: { operator: Operator.ADD, ...currentSource },
      });
      return;
    }

    move(currentOperationLineNumber - 1, newOperationLineNumber - 1);
    setVersion(version + 1);
  };

  React.useEffect(() => {
    if (toInsert) {
      insert(toInsert.index, toInsert.object);
      setToInsert(undefined);
      setVersion(version + 1);
    }
  }, [toInsert]);

  return (
    <ul className={'AknRuleOperation'}>
      {getSourceFormValue() && (
        <OperationLine
          key={sourceKey}
          baseFormName={'source'}
          sourceOrOperation={getSourceFormValue()}
          locales={locales}
          scopes={scopes}
          lineNumber={lineNumber}
          operationLineNumber={0}
          dropTarget={dropTarget}
          setDropTarget={setDropTarget}
          moveOperation={moveOperation}
          removeOperation={removeOperation}
          version={version}
        />
      )}
      {fields &&
        fields.map((sourceOrOperation: any, operationLineNumber) => {
          return (
            <OperationLine
              key={sourceOrOperation.id}
              baseFormName={`operation_list[${operationLineNumber}]`}
              sourceOrOperation={sourceOrOperation}
              locales={locales}
              scopes={scopes}
              lineNumber={lineNumber}
              operationLineNumber={operationLineNumber + 1}
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
