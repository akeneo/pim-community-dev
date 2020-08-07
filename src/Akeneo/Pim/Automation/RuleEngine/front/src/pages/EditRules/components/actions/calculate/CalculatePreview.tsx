import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  ConstantOperand,
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';
import { useControlledFormInputAction } from '../../../hooks';
import {
  Operation,
  Operator,
} from '../../../../../models/actions/Calculate/Operation';
import { AttributePreview } from '../attribute/AttributePreview';
import { useTranslate } from '../../../../../dependenciesTools/hooks';

const operationViewMapping: { [key: string]: string } = {
  add: '+',
  subtract: '-',
  multiply: 'x',
  divide: '/',
};

const OperandView: React.FC<{ operand: Operand }> = ({ operand }) => {
  const fieldOperand = (operand as FieldOperand).field;
  if (fieldOperand) {
    const fieldOperand = operand as FieldOperand;

    return <AttributePreview attributeCode={fieldOperand.field} />;
  }

  return (
    <span className='AknRule-constant-value'>
      {(operand as ConstantOperand).value}
    </span>
  );
};

const OperationView: React.FC<{ operation: Operation }> = ({ operation }) => {
  const getOperatorView = (operator: Operator): string => {
    if ('undefined' === operationViewMapping[operator]) {
      console.error(`The ${operator} operator is unknown`);
    }

    return operationViewMapping[operator] || '?';
  };

  return (
    <>
      {getOperatorView(operation.operator)} <OperandView operand={operation} />
    </>
  );
};

const RecursiveOperationView: React.FC<{
  operations: Operation[];
  source: Operand;
}> = ({ operations, source }) => {
  const operationsCopy = Array.from(operations);
  const lastOperation: Operation | undefined = operationsCopy.pop();
  if (!lastOperation) {
    return <OperandView operand={source} />;
  }

  return (
    <>
      {operationsCopy.length > 0 && '('}
      <RecursiveOperationView operations={operationsCopy} source={source} />
      {operationsCopy.length > 0 && ')'}{' '}
      <OperationView operation={lastOperation} />
    </>
  );
};

type Props = {
  lineNumber: number;
};

const CalculatePreview: React.FC<Props> = ({ lineNumber }) => {
  const translate = useTranslate();
  const { getFormValue, formName } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const getSourceFormValue = () => getFormValue('source');
  const getOperationListFormValue = () => getFormValue('operation_list');

  // Watch recursive is needed to trigger a render at each changes
  const { watch } = useFormContext();
  const watchRecursive = (field: any, path: string) => {
    if (Array.isArray(field)) {
      field.map((child: any, key: number) =>
        watchRecursive(child, `${path}[${key}]`)
      );
    } else if (typeof field === 'object' && null !== field) {
      Object.keys(field).map((key: string) =>
        watchRecursive(field[key], `${path}.${key}`)
      );
    } else {
      watch(path);
    }
  };
  const source = watch(formName('source'));
  const OperationList = watch(formName('operation_list'));
  watchRecursive(source, formName('source'));
  watchRecursive(OperationList, formName('operation_list'));

  return (
    <div className={'AknRulePreviewBox'}>
      <div className={'AknRulePreviewBox-title'}>
        {translate('pimee_catalog_rule.form.edit.preview')}
      </div>
      <div data-testid={'calculate-preview'}>
        {getSourceFormValue() && Array.isArray(getOperationListFormValue()) && (
          <RecursiveOperationView
            operations={getOperationListFormValue()}
            source={getSourceFormValue()}
          />
        )}
      </div>
    </div>
  );
};

export { CalculatePreview };
