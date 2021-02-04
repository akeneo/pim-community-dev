import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {CheckIcon} from '../../icons';

const StepCircle = styled.div<{completed: boolean} & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
  height: 32px;
  width: 32px;
  color: ${getColor('white')};
  background-color: ${({completed}) => (completed ? getColor('green', 100) : getColor('white'))};
  border-radius: 50%;
  border: 1px solid ${({completed}) => (completed ? 'transparent' : getColor('grey', 80))};
`;

// TODO RAC-331: Typography caption in uppercase
const StepLabel = styled.div`
  font-size: ${getFontSize('small')};
  font-weight: normal;
  color: ${getColor('grey', 120)};
  text-transform: uppercase;
`;

const StepContainer = styled.li<{completed: boolean} & AkeneoThemedProps>`
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;

  &:before {
    display: block;
    content: ' ';
    width: calc(100% - 34px);
    border-bottom-width: 1px;
    border-bottom-style: ${({completed}) => (completed ? 'solid' : 'dashed')};
    border-bottom-color: ${({completed}) => (completed ? getColor('green', 100) : getColor('grey', 80))};
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

type ProgressIndicatorStepProps = Override<
  React.HTMLAttributes<HTMLLIElement>,
  {
    /**
     * Mark the step as completed
     */
    completed?: boolean;

    /**
     * The label of the step
     */
    children: ReactNode;
  }
>;

const ProgressIndicatorStep = React.forwardRef<HTMLLIElement, ProgressIndicatorStepProps>(
  ({completed = false, children, ...rest}: ProgressIndicatorStepProps, forwardedRef: Ref<HTMLLIElement>) => {
    return (
      <StepContainer completed={completed} ref={forwardedRef} {...rest}>
        <StepCircle aria-hidden completed={completed}>
          {completed && <CheckIcon size={24} />}
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
/* @TODO StepIndicator or Stepper ? */
const ProgressIndicator = ({children, ...rest}: ProgressIndicatorProps) => {
  return <ProgressIndicatorContainer {...rest}>{children}</ProgressIndicatorContainer>;
};

ProgressIndicatorStep.displayName = 'ProgressIndicator.Step';
ProgressIndicator.Step = ProgressIndicatorStep;

export {ProgressIndicator};
