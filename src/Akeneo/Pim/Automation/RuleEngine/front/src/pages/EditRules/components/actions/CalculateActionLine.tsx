import React from 'react';
import { CalculateAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FallbackField } from '../FallbackField';
import {
  normalizeOperation,
  Operation,
  Operator,
} from '../../../../models/actions/Calculate/Operation';
import {
  ConstantOperand,
  FieldOperand,
  Operand,
} from '../../../../models/actions/Calculate/Operand';
import { useRegisterConsts } from "../../hooks/useRegisterConst";

const OperandView: React.FC<{ operand: Operand }> = ({ operand }) => {
  if (Object.keys(operand).includes('field')) {
    const fieldOperand = operand as FieldOperand;

    return (
      <FallbackField
        field={fieldOperand.field}
        scope={fieldOperand.scope || null}
        locale={fieldOperand.locale || null}
      />
    );
  }

  return (
    <span className='AknRule-attribute'>
      {(operand as ConstantOperand).value}
    </span>
  );
};

const AddView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by adding&nbsp;
      <OperandView operand={operand} />
      {source && (
        <>
          &nbsp;to&nbsp;
          <OperandView operand={source} />
        </>
      )}
    </>
  );
};

const SubstractView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by subtracting&nbsp;
      <OperandView operand={operand} />
      {source && (
        <>
          &nbsp;from&nbsp;
          <OperandView operand={source} />
        </>
      )}
    </>
  );
};

const MultiplyView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by multiplying&nbsp;
      {source && (
        <>
          <OperandView operand={source} />
          &nbsp;
        </>
      )}
      by&nbsp;
      <OperandView operand={operand} />
    </>
  );
};

const DivideView: React.FC<{ operand: Operand; source: Operand | null }> = ({
  operand,
  source,
}) => {
  return (
    <>
      by dividing&nbsp;
      {source && (
        <>
          <OperandView operand={source} />
          &nbsp;
        </>
      )}
      by&nbsp;
      <OperandView operand={operand} />
    </>
  );
};

type Props = {
  action: CalculateAction;
} & ActionLineProps;

const CalculateActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  useRegisterConsts(
    {
      type: 'calculate',
      destination: action.destination,
      source: action.source,
      operation_list: action.operation_list.map((operation: Operation) =>
        normalizeOperation(operation)
      ),
      // TODO: uncomment when it will be ready
      // round_precision: action.round_precision,
    },
    `content.actions[${lineNumber}]`
  );

  return (
    <ActionTemplate
      title='Calculate Action'
      helper='This feature is under development. Please use the import to manage your rules.'
      legend='This feature is under development. Please use the import to manage your rules.'
      handleDelete={handleDelete}>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-bodyRow AknGrid-bodyRow--highlight'>
          <div className='AknGrid-bodyCell'>
            <FallbackField
              field={action.destination.field}
              scope={action.destination.scope || null}
              locale={action.destination.locale || null}
            />
            &nbsp;is calculated&nbsp;
            {action.operation_list.map((operation: Operation, key: number) => (
              <React.Fragment key={key}>
                {Operator.ADD === operation.operator && (
                  <AddView
                    operand={operation.operand}
                    source={key === 0 ? action.source : null}
                  />
                )}
                {Operator.SUBSTRACT === operation.operator && (
                  <SubstractView
                    operand={operation.operand}
                    source={key === 0 ? action.source : null}
                  />
                )}
                {Operator.MULTIPLY === operation.operator && (
                  <MultiplyView
                    operand={operation.operand}
                    source={key === 0 ? action.source : null}
                  />
                )}
                {Operator.DIVIDE === operation.operator && (
                  <DivideView
                    operand={operation.operand}
                    source={key === 0 ? action.source : null}
                  />
                )}

                {key < action.operation_list.length - 1 && ', then '}
              </React.Fragment>
            ))}
            .
          </div>
        </div>
      </div>
    </ActionTemplate>
  );
};

export { CalculateActionLine };
