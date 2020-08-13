import React from 'react';
import { useFieldArray, useFormContext } from 'react-hook-form';
import { Operation, } from '../../../../../models/actions/Calculate/Operation';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { useControlledFormInputAction } from '../../../hooks';
import { DropTarget, OperationLine } from "./OperationLine";

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
  const translate = useTranslate();
  const { formName } = useControlledFormInputAction<string | null>(lineNumber);
  const [dropTarget, setDropTarget] = React.useState<DropTarget | null>(null);
  const [version, setVersion] = React.useState<number>(1);
  const { setError } = useFormContext();

  const { fields, remove, move } = useFieldArray({
    name: formName('full_operation_list'),
  });

  const removeOperation = (lineToRemove: number) => () => {
    const numberOfOperationsBeforeRemove = fields.length;
    remove(lineToRemove);
    setVersion(version + 1);
    if (numberOfOperationsBeforeRemove <= 2) {
      setError(
        formName('full_operation_list'),
        'custom',
        translate('pimee_catalog_rule.exceptions.two_operations_are_required')
      );
    }
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
