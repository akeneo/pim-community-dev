import React, {Ref, ReactNode, isValidElement, HTMLAttributes, forwardRef, Children, cloneElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {CheckIcon} from '../../icons';

type StepState = 'done' | 'inprogress' | 'todo';

const StepCircle = styled.div<{state: StepState} & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
  height: 32px;
  width: 32px;
  color: ${getColor('white')};
  background-color: ${({state}) =>
    state === 'todo' ? getColor('white') : state === 'inprogress' ? getColor('green', 100) : getColor('green', 100)};
  border-radius: 50%;
  border: 1px solid ${({state}) => (state !== 'todo' ? 'transparent' : getColor('grey', 80))};
`;

const StepLabel = styled.div`
  font-size: ${getFontSize('small')};
  font-weight: normal;
  color: ${getColor('grey', 120)};
  text-transform: uppercase;
`;

const StepContainer = styled.li<StepProps & AkeneoThemedProps>`
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  opacity: ${({disabled}) => (disabled ? 0.6 : 1)};

  &:before {
    display: block;
    content: ' ';
    width: calc(100% - 34px);
    border-bottom-width: 1px;
    border-bottom-style: ${({state}) => ('todo' === state ? 'dashed' : 'solid')};
    border-bottom-color: ${({state}) => ('todo' !== state ? getColor('green', 100) : getColor('grey', 80))};
    position: relative;
    left: -50%;
    top: 17px;
  }
`;

const ProgressIndicatorContainer = styled.ul`
  display: flex;
  justify-content: space-between;

  ${StepContainer}:first-child:before {
    display: none;
    border: none;
  }
`;

type StepProps = Override<
  HTMLAttributes<HTMLLIElement>,
  {
    /**
     * Set to true if the step is the current step in the progress indicator.
     */
    current?: boolean;

    /**
     * The state of the step.
     * This prop is not mandatory as the component can calculate its state based on the `current` prop.
     */
    state?: StepState;

    /**
     * Define if the step is disabled.
     */
    disabled?: boolean;

    /**
     * The label of the step.
     */
    children?: ReactNode;
  }
>;

const Step = forwardRef<HTMLLIElement, StepProps>(
  ({state, children, disabled, onClick, ...rest}: StepProps, forwardedRef: Ref<HTMLLIElement>) => {
    if (undefined === state) {
      throw new Error('ProgressIndicator.Step cannot be used outside a ProgressIndicator component');
    }

    return (
      <StepContainer
        aria-current={'inprogress' === state ? 'step' : undefined}
        state={state}
        ref={forwardedRef}
        aria-disabled={disabled}
        onClick={disabled ? undefined : onClick}
        disabled={disabled}
        {...rest}
      >
        <StepCircle aria-hidden state={state}>
          {'done' === state && <CheckIcon size={24} />}
        </StepCircle>
        <StepLabel>{children}</StepLabel>
      </StepContainer>
    );
  }
);

type ProgressIndicatorProps = Override<
  HTMLAttributes<HTMLUListElement>,
  {
    /**
     * The progress steps.
     */
    children?: ReactNode;
  }
>;

/**
 * Progress indicator display progress through a sequence of logical and numbered steps.
 */
const ProgressIndicator = ({children, ...rest}: ProgressIndicatorProps) => {
  const currentStepIndex = Children.toArray(children).reduce((result, child, index) => {
    return isValidElement<StepProps>(child) && child.type === Step && child.props.current === true ? index : result;
  }, 0) as number;

  const decoratedChildren = Children.map(children, (child, index) => {
    if (!(isValidElement<StepProps>(child) && child.type === Step)) {
      return child;
    }

    return undefined === child.props.state
      ? cloneElement(child, {
          state: index > currentStepIndex ? 'todo' : index < currentStepIndex ? 'done' : 'inprogress',
        })
      : child;
  });

  return (
    <ProgressIndicatorContainer aria-label="progress" {...rest}>
      {decoratedChildren}
    </ProgressIndicatorContainer>
  );
};

Step.displayName = 'ProgressIndicator.Step';
ProgressIndicator.Step = Step;

export {ProgressIndicator};
