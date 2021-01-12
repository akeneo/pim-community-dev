import React, {ReactNode, Ref, SyntheticEvent} from 'react';
import styled, {css, keyframes} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize, PlaceholderStyle} from '../../theme';
import {CheckIcon, CheckPartialIcon} from '../../icons';
import {useId, useShortcut} from '../../hooks';
import {Key, Override} from '../../shared';

const checkTick = keyframes`
  to {
    stroke-dashoffset: 0;
  }
`;

const uncheckTick = keyframes`
  to {
    stroke-dashoffset: 17px;
  }
`;

const Container = styled.div`
  display: flex;
`;

const TickIcon = styled(CheckIcon)`
  animation: ${uncheckTick} 0.2s ease-in forwards;
  opacity: 0;
  stroke-dasharray: 17px;
  stroke-dashoffset: 0;
  transition-delay: 0.2s;
  transition: opacity 0.1s ease-out;
`;

const CheckboxContainer = styled.div<{checked: boolean; readOnly: boolean} & AkeneoThemedProps>`
  background-color: transparent;
  height: 20px;
  width: 20px;
  border: 1px solid ${getColor('grey80')};
  border-radius: 3px;
  overflow: hidden;
  background-color: ${getColor('grey20')};
  transition: background-color 0.2s ease-out;
  box-sizing: border-box;
  color: ${getColor('white')};

  ${props =>
    props.checked &&
    css`
      background-color: ${getColor('blue100')};
      border-color: ${getColor('blue100')};

      ${TickIcon} {
        animation-delay: 0.2s;
        animation: ${checkTick} 0.2s ease-out forwards;
        stroke-dashoffset: 17px;
        opacity: 1;
        transition-delay: 0s;
      }
    `}

  ${props =>
    props.checked &&
    props.readOnly &&
    css`
      background-color: ${getColor('blue20')};
      border-color: ${getColor('blue40')};
    `}

  ${props =>
    !props.checked &&
    props.readOnly &&
    css`
      background-color: ${getColor('grey60')};
      border-color: ${getColor('grey100')};
    `}
`;

const LabelContainer = styled.label<{readOnly: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey140')};
  font-weight: 400;
  font-size: ${getFontSize('big')};
  margin-left: 10px;

  ${props =>
    props.readOnly &&
    css`
      color: ${getColor('grey100')};
    `}
`;

type CheckboxChecked = boolean | 'mixed';

type CheckboxProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * State of the Checkbox.
     */
    checked: CheckboxChecked;

    /**
     * Displays the value of the input, but does not allow changes.
     */
    readOnly?: boolean;

    /**
     * The handler called when clicking on Checkbox.
     */
    onChange?: (value: CheckboxChecked, event: SyntheticEvent) => void;

    /**
     * Label of the checkbox.
     */
    children?: ReactNode;
  }
>;

/**
 * The checkboxes are applied when users can select all, several, or none of the options from a given list.
 */
const Checkbox = React.forwardRef<HTMLDivElement, CheckboxProps>(
  (
    {checked = false, onChange, readOnly = false, children, title, ...rest}: CheckboxProps,
    forwardedRef: Ref<HTMLDivElement>
  ): React.ReactElement => {
    const checkboxId = useId('checkbox_');
    const labelId = useId('label_');

    const isChecked = true === checked;
    const isMixed = 'mixed' === checked;

    const handleChange = (event: SyntheticEvent) => {
      if (!onChange || readOnly) return;

      switch (checked) {
        case true:
          onChange(false, event);
          break;
        case 'mixed':
        case false:
          onChange(true, event);
          break;
      }

      event.stopPropagation();
    };
    const ref = useShortcut(Key.Space, handleChange, forwardedRef);
    const forProps = children
      ? {
          'aria-labelledby': labelId,
          id: checkboxId,
        }
      : {};

    return (
      <Container {...rest}>
        <CheckboxContainer
          checked={isChecked || isMixed}
          readOnly={readOnly}
          title={title}
          role="checkbox"
          ref={ref}
          aria-checked={isChecked}
          tabIndex={readOnly ? -1 : 0}
          onClick={handleChange}
          {...forProps}
        >
          {isMixed ? <CheckPartialIcon size={18} /> : <TickIcon size={20} />}
        </CheckboxContainer>
        {children ? (
          <LabelContainer onClick={handleChange} id={labelId} readOnly={readOnly} htmlFor={checkboxId}>
            {children}
          </LabelContainer>
        ) : null}
      </Container>
    );
  }
);

Object.assign(Checkbox, {
  Skeleton: styled(Checkbox)`
    * {
      ${PlaceholderStyle}
    }
  `,
});

export {Checkbox};
export type {CheckboxChecked};
