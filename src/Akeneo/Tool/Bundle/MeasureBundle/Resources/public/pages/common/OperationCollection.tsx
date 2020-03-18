import React, {useState, useContext} from 'react';
import styled from 'styled-components';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Button} from 'akeneomeasure/shared/components/Button';
import {Operation, Operator, emptyOperation, MAX_OPERATION_COUNT} from 'akeneomeasure/model/measurement-family';

const Operation = styled.div`
  border: 1px solid ${props => props.theme.color.grey80};
`;
const OperationValue = styled.input``;
const OperationOperator = styled.span``;
const OperatorSelector = styled.div`
  z-index: 804;
`;
const OperatorOption = styled.div`
  z-index: 804;
`;
const RemoveOperation = styled.div`
  width: 50px;
  height: 50px;
  background: red;
`;
// const OperatorSelectorMask = styled.div`
//   position: fixed;
//   top: 0;
//   left: 0;
//   width: 100%;
//   height: 100%;
//   z-index: 803;
// `;

type OperationCollectionProps = {
  operations: Operation[];
  onOperationsChange: (operations: Operation[]) => void;
};

const OperationCollection = ({operations, onOperationsChange}: OperationCollectionProps) => {
  const __ = useContext(TranslateContext);
  const [openOperatorSelector, setOpenOperatorSelector] = useState<number | null>(null);

  return (
    <>
      {operations.map((operation: Operation, index: number) => (
        <Operation key={index}>
          <OperationValue
            value={operation.value}
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              onOperationsChange(
                operations.map((operation: Operation, currentIndex: number) => {
                  if (currentIndex !== index) {
                    return operation;
                  }

                  return {...operation, value: event.currentTarget.value};
                })
              );
            }}
          />
          <OperationOperator onClick={() => setOpenOperatorSelector(index)}>{operation.operator}</OperationOperator>
          {openOperatorSelector === index && (
            <>
              {/* <OperatorSelectorMask onClick={() => setOpenOperatorSelector(null)} /> */}
              <OperatorSelector>
                {Object.values(Operator).map((operator: string) => (
                  <OperatorOption
                    key={operator}
                    onClick={() => {
                      setOpenOperatorSelector(null);
                      onOperationsChange(
                        operations.map((operation: Operation, currentIndex: number) => {
                          if (currentIndex !== index) {
                            return operation;
                          }

                          return {...operation, operator};
                        })
                      );
                    }}
                  >
                    {__(`measurements.unit.operator.${operator}`)}
                  </OperatorOption>
                ))}
              </OperatorSelector>
            </>
          )}
          {operations.length > 1 && (
            <RemoveOperation
              onClick={() => {
                setOpenOperatorSelector(null);
                onOperationsChange(
                  operations.filter((_operation: Operation, currentIndex: number) => index !== currentIndex)
                );
              }}
            />
          )}
        </Operation>
      ))}
      {operations.length < MAX_OPERATION_COUNT && (
        <Button
          color="grey"
          outline
          onClick={() => {
            onOperationsChange([...operations, emptyOperation()]);
          }}
        >
          {__('measurements.unit.operation.add')}
        </Button>
      )}
    </>
  );
};

export {OperationCollection};
