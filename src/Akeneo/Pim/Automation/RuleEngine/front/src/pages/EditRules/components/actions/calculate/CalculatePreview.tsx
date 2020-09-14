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
import styled from 'styled-components';
import { Translate } from '../../../../../dependenciesTools';

const UnknownValue = styled.span`
  color: ${({ theme }): string => theme.color.red100};
`;

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

const OperationView: React.FC<{
  operation: Operation;
  translate: Translate;
}> = ({ operation, translate }) => {
  const getOperatorView = (operator: Operator): string | JSX.Element => {
    if ('undefined' === operationViewMapping[operator]) {
      console.error(`The ${operator} operator is unknown`);
    }

    return (
      operationViewMapping[operator] || (
        <UnknownValue>
          {translate(
            `pimee_catalog_rule.form.edit.actions.calculate.operator.choose`
          )}
        </UnknownValue>
      )
    );
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
  translate: Translate;
}> = ({ operations, source, translate }) => {
  const operationsCopy = Array.from(operations);
  const lastOperation: Operation | undefined = operationsCopy.pop();
  if (!lastOperation) {
    return <OperandView operand={source} />;
  }

  return (
    <>
      {operationsCopy.length > 0 && '('}
      <RecursiveOperationView
        operations={operationsCopy}
        source={source}
        translate={translate}
      />
      {operationsCopy.length > 0 && ')'}{' '}
      <OperationView operation={lastOperation} translate={translate} />
    </>
  );
};

type Props = {
  lineNumber: number;
};

const CalculatePreview: React.FC<Props> = ({ lineNumber }) => {
  const translate = useTranslate();
  const { watch } = useFormContext();
  const { formName } = useControlledFormInputAction<string | null>(lineNumber);
  const getFullOperationListFormValue = () =>
    watch(formName('full_operation_list'));

  return (
    <div className={'AknRulePreviewBox'}>
      <div className={'AknRulePreviewBox-title'}>
        {translate('pimee_catalog_rule.form.edit.preview')}
      </div>
      <div data-testid={'calculate-preview'}>
        {Array.isArray(getFullOperationListFormValue()) &&
          getFullOperationListFormValue().length > 0 && (
            <RecursiveOperationView
              operations={getFullOperationListFormValue().slice(1)}
              source={getFullOperationListFormValue()[0]}
              translate={translate}
            />
          )}
      </div>
    </div>
  );
};

export { CalculatePreview };
