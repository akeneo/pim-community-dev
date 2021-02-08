import React, {Ref, ReactNode, isValidElement} from 'react';
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

// TODO RAC-331: Typography caption in uppercase
const StepLabel = styled.div`
  font-size: ${getFontSize('small')};
  font-weight: normal;
  color: ${getColor('grey', 120)};
  text-transform: uppercase;
`;

const StepContainer = styled.li<{state: StepState} & AkeneoThemedProps>`
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;

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
  React.HTMLAttributes<HTMLLIElement>,
  {
    /**
     * Set to true if the step is the current step in the progress indicator
     */
    current?: boolean;

    /**
     * This property is handheld by the ProgressIndicator component. You should not set it yourself.
     * @private
     */
    state?: StepState;

    /**
     * The label of the step
     */
    children: ReactNode;
  }
>;

const Step = React.forwardRef<HTMLLIElement, StepProps>(
  ({state, children, ...rest}: StepProps, forwardedRef: Ref<HTMLLIElement>) => {
    if (undefined === state) {
      throw new Error('ProgressIndicator.Step cannot be used outside a ProgressIndicator component');
    }

    return (
      <StepContainer
        aria-current={'inprogress' === state ? 'step' : undefined}
        state={state}
        ref={forwardedRef}
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
  React.HTMLAttributes<HTMLUListElement>,
  {
    /**
     * The progress steps
     */
    children?: ReactNode;
  }
>;

/**
 * Progress indicator display progress through a sequence of logical and numbered steps
 */
const ProgressIndicator = ({children, ...rest}: ProgressIndicatorProps) => {
  const currentStepIndex = React.Children.toArray(children).reduce((result, child, index) => {
    return isValidElement<StepProps>(child) && child.type === Step && child.props.current === true ? index : result;
  }, 0);

  const decoratedChildren = React.Children.map(children, (child, index) => {
    if (!(isValidElement(child) && child.type === Step)) {
      throw new Error('ProgressIndicator only accepts `ProgressIndicator.Step` elements as children');
    }

    return React.cloneElement(child, {
      state: index > currentStepIndex ? 'todo' : index < currentStepIndex ? 'done' : 'inprogress',
    });
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
