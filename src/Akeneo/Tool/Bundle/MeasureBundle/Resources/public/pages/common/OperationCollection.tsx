import React, {useState, useContext, useCallback, useEffect} from 'react';
import styled, {css} from 'styled-components';
import {ConfigContext} from 'akeneomeasure/context/config-context';
import {
  ArrowDownIcon,
  ArrowIcon,
  CloseIcon,
  Button,
  TextInput,
  getColor,
  getFontSize,
  useShortcut,
  Key,
  Helper,
  AkeneoThemedProps,
  LockIcon,
} from 'akeneo-design-system';
import {Operation, Operator, emptyOperation} from 'akeneomeasure/model/operation';
import {useLocalizedNumber} from 'akeneomeasure/shared/hooks/use-localized-number';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError, filterErrors, getErrorsForPath, formatParameters} from '@akeneo-pim-community/shared';

const Container = styled.div<{level: number}>`
  position: relative;
  margin-left: ${({level}) => (level > 1 ? 24 * (level - 1) : 0)}px;

  :not(:first-child) {
    margin-top: 10px;
  }
`;

const OperationLine = styled.div`
  display: flex;
  align-items: center;
  color: ${getColor('grey', 100)};
`;

const OperationInput = styled.div`
  flex: 1;
  position: relative;
`;

const OperationCollectionLabel = styled.label`
  margin-bottom: 8px;
`;

const StyledArrow = styled(ArrowIcon)`
  margin: 0 4px 10px 2px;
`;

const OperationOperator = styled.span<{readOnly: boolean} & AkeneoThemedProps>`
  position: absolute;
  top: 1px;
  right: 1px;
  padding: 0 10px;
  height: 38px;
  background-color: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: ${({onClick}) => (onClick ? 'pointer' : 'default')};
`;

const OperatorSelector = styled.div`
  z-index: 804;
  position: absolute;
  top: 0;
  right: 1px;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  width: 200px;
  padding: 20px;
  background-color: ${getColor('white')};
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
  color: ${getColor('brand', 100)};
  padding-bottom: 15px;
  border-bottom: 1px solid ${getColor('brand', 100)};
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
`;

const OperatorOption = styled.div<{isSelected?: boolean}>`
  margin-top: 18px;
  cursor: pointer;

  ${props =>
    props.isSelected &&
    css`
      color: ${getColor('brand', 100)};
      font-style: italic;
      font-weight: bold;
    `}
`;

const RemoveOperationButton = styled.div`
  display: flex;
  cursor: pointer;
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

const SpacedHelper = styled(Helper)<{hasOffset?: boolean}>`
  margin-top: 5px;
  margin-left: ${({hasOffset}) => (hasOffset ? 24 : 0)}px;
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
  const translate = useTranslate();
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
    <div>
      <OperationCollectionLabel>
        {translate('measurements.unit.convert_from_standard')} {translate('pim_common.required_label')}
      </OperationCollectionLabel>
      {operations.map((operation: Operation, index: number) => {
        const operationErrors = filterErrors(errors, `[${index}]`);

        return (
          <Container key={index} level={index}>
            <OperationLine>
              {0 < index && <StyledArrow size={18} />}
              <OperationInput>
                <TextInput
                  placeholder={translate('measurements.unit.operation.placeholder')}
                  value={formatNumber(operation.value)}
                  readOnly={readOnly}
                  invalid={!shouldHideErrors && 0 < operationErrors.length}
                  onChange={(value: string) =>
                    onOperationsChange(
                      operations.map((operation: Operation, currentIndex: number) =>
                        currentIndex === index ? {...operation, value: unformatNumber(value)} : operation
                      )
                    )
                  }
                />
                <OperationOperator
                  readOnly={readOnly}
                  onClick={readOnly ? undefined : () => setOpenOperatorSelector(index)}
                >
                  {translate(`measurements.unit.operator.${operation.operator}`)}
                  {readOnly ? <LockIcon size={18} /> : <ArrowDownIcon size={18} />}
                </OperationOperator>
              </OperationInput>
              {!readOnly && 1 < operations.length && (
                <RemoveOperationButton
                  title={translate('pim_common.remove')}
                  onClick={() => {
                    closeOperatorSelector();
                    setShouldHideErrors(true);
                    onOperationsChange(
                      operations.filter((_operation: Operation, currentIndex: number) => index !== currentIndex)
                    );
                  }}
                >
                  <CloseIcon size={18} />
                </RemoveOperationButton>
              )}
            </OperationLine>
            {!readOnly && openOperatorSelector === index && (
              <>
                <OperatorSelectorMask onClick={closeOperatorSelector} />
                <OperatorSelector>
                  <OperatorSelectorLabel>{translate('measurements.unit.operator.select')}</OperatorSelectorLabel>
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
                      {translate(`measurements.unit.operator.${operator}`)}
                    </OperatorOption>
                  ))}
                </OperatorSelector>
              </>
            )}
            {formatParameters(shouldHideErrors ? [] : operationErrors).map((error, key) => (
              <SpacedHelper key={key} hasOffset={0 < index} level="error" inline={true}>
                {translate(error.messageTemplate, error.parameters, error.plural)}
              </SpacedHelper>
            ))}
          </Container>
        );
      })}
      {!readOnly && (
        <Footer>
          <Button
            level="tertiary"
            ghost={true}
            disabled={config.operations_max <= operations.length}
            onClick={() => onOperationsChange([...operations, emptyOperation()])}
          >
            {translate('measurements.unit.operation.add')}
          </Button>
        </Footer>
      )}
      {formatParameters(shouldHideErrors ? [] : getErrorsForPath(errors, '')).map((error, key) => (
        <SpacedHelper key={key} level="error" inline={true}>
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </SpacedHelper>
      ))}
    </div>
  );
};

export {OperationCollection};
