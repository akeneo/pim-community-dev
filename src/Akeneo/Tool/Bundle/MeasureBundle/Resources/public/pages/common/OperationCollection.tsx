import React, {useState, useContext, useCallback, useEffect} from 'react';
import styled, {css} from 'styled-components';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Button, TransparentButton} from 'akeneomeasure/shared/components/Button';
import {DownIcon} from 'akeneomeasure/shared/icons/DownIcon';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {CloseIcon} from 'akeneomeasure/shared/icons/CloseIcon';
import {SubArrowRightIcon} from 'akeneomeasure/shared/icons/SubArrowRightIcon';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';
import {Operation, Operator, emptyOperation, MAX_OPERATION_COUNT} from 'akeneomeasure/model/operation';
import {ValidationError, filterErrors, getErrorsForPath} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';

const Container = styled.div<{level: number}>`
  position: relative;
  margin-left: ${props => (props.level > 1 ? 24 * (props.level - 1) : 0)}px;

  :not(:first-child) {
    margin-top: 10px;
  }
`;

const OperationLine = styled.div`
  display: flex;
  align-items: center;
`;

const OperationCollectionLabel = styled.div`
  margin-bottom: 10px;
`;

const StyledArrow = styled(SubArrowRightIcon)`
  margin: 0 4px 10px 2px;
`;

const OperationContainer = styled.div<{readOnly: boolean}>`
  border: 1px solid ${props => props.theme.color.grey80};
  background-color: ${props => (props.readOnly ? props.theme.color.grey70 : 'inherit')};
  cursor: ${props => (props.readOnly ? 'not-allowed' : 'inherit')};
  height: 40px;
  display: flex;
  flex: 1;
  align-items: center;
  padding: 0 15px;
`;

const OperationValue = styled.input<{readOnly: boolean}>`
  background-color: transparent;
  border: none;
  flex: 1;
  color: ${props => (props.readOnly ? props.theme.color.grey120 : props.theme.color.grey140)};
  outline: none;
  cursor: inherit;
`;

const OperationOperator = styled.span`
  text-transform: uppercase;
  display: flex;
  align-items: center;
  padding-left: 10px;
  color: ${props => props.theme.color.grey100};
  cursor: pointer;

  span:first-child {
    margin-right: 10px;
  }
`;

const OperatorSelector = styled.div`
  z-index: 804;
  position: absolute;
  top: 0;
  right: 1px;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  width: 200px;
  padding: 20px;
  background-color: ${props => props.theme.color.white};
  display: flex;
  flex-direction: column;
`;

const OperatorSelectorLabel = styled.label`
  color: ${props => props.theme.color.purple100};
  padding-bottom: 15px;
  border-bottom: 1px solid ${props => props.theme.color.purple100};
  text-transform: uppercase;
  font-size: ${props => props.theme.fontSize.small};
`;

const OperatorOption = styled.div<{isSelected?: boolean}>`
  margin-top: 18px;
  cursor: pointer;

  ${props =>
    props.isSelected &&
    css`
      color: ${props => props.theme.color.purple100};
      font-style: italic;
      font-weight: bold;
    `}
`;

const RemoveOperationButton = styled(TransparentButton)`
  margin-left: 10px;
`;

const OperatorSelectorMask = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 800;
`;

const Footer = styled.div`
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
`;

type OperationCollectionProps = {
  operations: Operation[];
  errors?: ValidationError[];
  readOnly?: boolean;
  onOperationsChange: (operations: Operation[]) => void;
};

const OperationCollection = ({
  operations,
  errors = [],
  readOnly = false,
  onOperationsChange,
}: OperationCollectionProps) => {
  const __ = useContext(TranslateContext);
  const [openOperatorSelector, setOpenOperatorSelector] = useState<number | null>(null);

  const closeOperatorSelector = useCallback(() => setOpenOperatorSelector(null), [setOpenOperatorSelector]);

  useShortcut(Key.Escape, closeOperatorSelector);

  // As the operations are not indexed, we need to hide the errors as soon as th user removes an operation
  // To avoid to display an error on a previous operation
  const [shouldHideErrors, setShouldHideErrors] = useState(false);
  useEffect(() => {
    setShouldHideErrors(false);
  }, [JSON.stringify(errors)]);

  return (
    <>
      <OperationCollectionLabel>
        {__('measurements.unit.convert_from_standard')} {__('pim_common.required_label')}
      </OperationCollectionLabel>
      {operations.map((operation: Operation, index: number) => {
        const operationErrors = filterErrors(errors, `[${index}]`);

        return (
          <Container key={index} level={index}>
            <OperationLine>
              {0 < index && <StyledArrow color={akeneoTheme.color.grey100} size={18} />}
              <OperationContainer readOnly={readOnly}>
                <OperationValue
                  role="operation-value-input"
                  value={operation.value}
                  disabled={readOnly}
                  readOnly={readOnly}
                  onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                    onOperationsChange(
                      operations.map((operation: Operation, currentIndex: number) =>
                        currentIndex === index ? {...operation, value: event.currentTarget.value} : operation
                      )
                    );
                  }}
                />
                <OperationOperator onClick={() => setOpenOperatorSelector(index)}>
                  <span>{__(`measurements.unit.operator.${operation.operator}`)}</span>
                  {!readOnly && <DownIcon color={akeneoTheme.color.grey100} size={18} />}
                </OperationOperator>
                {!readOnly && openOperatorSelector === index && (
                  <>
                    <OperatorSelectorMask onClick={closeOperatorSelector} />
                    <OperatorSelector>
                      <OperatorSelectorLabel>{__('measurements.unit.operator.select')}</OperatorSelectorLabel>
                      {Object.values(Operator).map((operator: string) => (
                        <OperatorOption
                          key={operator}
                          isSelected={operator === operation.operator}
                          onClick={() => {
                            closeOperatorSelector();
                            onOperationsChange(
                              operations.map((operation: Operation, currentIndex: number) =>
                                currentIndex === index ? {...operation, operator} : operation
                              )
                            );
                          }}
                        >
                          {__(`measurements.unit.operator.${operator}`)}
                        </OperatorOption>
                      ))}
                    </OperatorSelector>
                  </>
                )}
              </OperationContainer>
              {1 < operations.length && (
                <RemoveOperationButton
                  title={__('pim_common.remove')}
                  onClick={() => {
                    closeOperatorSelector();
                    setShouldHideErrors(true);
                    onOperationsChange(
                      operations.filter((_operation: Operation, currentIndex: number) => index !== currentIndex)
                    );
                  }}
                >
                  <CloseIcon color={akeneoTheme.color.grey100} size={18} />
                </RemoveOperationButton>
              )}
            </OperationLine>
            <InputErrors errors={shouldHideErrors ? [] : operationErrors} />
          </Container>
        );
      })}
      {!readOnly && (
        <Footer>
          <Button
            color="grey"
            outline={true}
            disabled={MAX_OPERATION_COUNT <= operations.length}
            onClick={() => onOperationsChange([...operations, emptyOperation()])}
          >
            {__('measurements.unit.operation.add')}
          </Button>
        </Footer>
      )}
      <InputErrors errors={shouldHideErrors ? [] : getErrorsForPath(errors, '')} />
    </>
  );
};

export {OperationCollection};
