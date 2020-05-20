import React, {useState, useContext, useCallback, useEffect, ChangeEvent} from 'react';
import styled, {css, ThemeContext} from 'styled-components';
import {ConfigContext} from 'akeneomeasure/context/config-context';
import {DownIcon} from 'akeneomeasure/shared/icons/DownIcon';
import {LockIcon} from 'akeneomeasure/shared/icons/LockIcon';
import {CloseIcon} from 'akeneomeasure/shared/icons/CloseIcon';
import {SubArrowRightIcon} from 'akeneomeasure/shared/icons/SubArrowRightIcon';
import {useShortcut} from 'akeneomeasure/shared/hooks/use-shortcut';
import {Key} from 'akeneomeasure/shared/key';
import {Operation, Operator, emptyOperation} from 'akeneomeasure/model/operation';
import {ValidationError, filterErrors, getErrorsForPath} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {Input, InputContainer} from 'akeneomeasure/shared/components/TextField';
import {useLocalizedNumber} from 'akeneomeasure/shared/hooks/use-localized-number';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button, TransparentButton} from '@akeneo-pim-community/shared';

const AknFieldContainer = styled.div`
  margin-bottom: 20px;
  position: relative;
`;

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

const OperationOperator = styled.span<{readOnly: boolean}>`
  text-transform: uppercase;
  display: flex;
  align-items: center;
  padding-left: 10px;
  color: ${props => props.theme.color.grey100};
  ${props =>
    !props.readOnly &&
    css`
      cursor: pointer;
    `}

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

  &:after {
    bottom: -25px;
    content: '';
    display: block;
    height: 5px;
    position: relative;
    width: 100%;
  }
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
  const __ = useTranslate();
  const akeneoTheme = useContext(ThemeContext);
  const config = useContext(ConfigContext);
  const [openOperatorSelector, setOpenOperatorSelector] = useState<number | null>(null);
  const [formatNumber, unformatNumber] = useLocalizedNumber();

  const closeOperatorSelector = useCallback(() => setOpenOperatorSelector(null), [setOpenOperatorSelector]);

  useShortcut(Key.Escape, closeOperatorSelector);

  // As the operations are not indexed, we need to hide the errors as soon as the user removes an operation
  // To avoid to display an error on a previous operation
  const [shouldHideErrors, setShouldHideErrors] = useState(false);
  useEffect(() => {
    setShouldHideErrors(false);
  }, [JSON.stringify(errors)]);

  return (
    <AknFieldContainer>
      <OperationCollectionLabel>
        {__('measurements.unit.convert_from_standard')} {__('pim_common.required_label')}
      </OperationCollectionLabel>
      {operations.map((operation: Operation, index: number) => {
        const operationErrors = filterErrors(errors, `[${index}]`);

        return (
          <Container key={index} level={index}>
            <OperationLine>
              {0 < index && <StyledArrow color={akeneoTheme.color.grey100} size={18} />}
              <InputContainer readOnly={readOnly} invalid={0 < operationErrors.length}>
                <Input
                  role="operation-value-input"
                  placeholder={__('measurements.unit.operation.placeholder')}
                  value={formatNumber(operation.value)}
                  disabled={readOnly}
                  readOnly={readOnly}
                  onChange={(event: ChangeEvent<HTMLInputElement>) =>
                    onOperationsChange(
                      operations.map((operation: Operation, currentIndex: number) =>
                        currentIndex === index
                          ? {...operation, value: unformatNumber(event.currentTarget.value)}
                          : operation
                      )
                    )
                  }
                />
                <OperationOperator readOnly={readOnly} onClick={() => !readOnly && setOpenOperatorSelector(index)}>
                  <span>{__(`measurements.unit.operator.${operation.operator}`)}</span>
                  {readOnly ? (
                    <LockIcon color={akeneoTheme.color.grey100} size={18} />
                  ) : (
                    <DownIcon color={akeneoTheme.color.grey100} size={18} />
                  )}
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
              </InputContainer>
              {!readOnly && 1 < operations.length && (
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
            disabled={config.operations_max <= operations.length}
            onClick={() => onOperationsChange([...operations, emptyOperation()])}
          >
            {__('measurements.unit.operation.add')}
          </Button>
        </Footer>
      )}
      <InputErrors errors={shouldHideErrors ? [] : getErrorsForPath(errors, '')} />
    </AknFieldContainer>
  );
};

export {OperationCollection};
