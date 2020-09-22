import React, {Ref, useState} from 'react';
import styled, {css, keyframes} from 'styled-components';
import {AkeneoThemedProps, getColor} from 'theme';
import {CheckIcon, PartialCheckIcon} from 'icons';
import {useShortcut} from 'hooks/use-shortcut';
import {Key} from 'shared/key';
import {uuid} from 'shared/uuid';

const getAriaChecked = (status: CheckboxStatus): string => {
  switch (status) {
    case 'checked':
      return 'true';
    case 'unchecked':
      return 'false';
    case 'undetermined':
      return 'mixed';
  }
};

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
  font-size: 15px;
  padding-left: 10px;

  ${props =>
    props.readOnly &&
    css`
      color: ${getColor('grey100')};
    `}
`;

type CheckboxStatus = 'checked' | 'unchecked' | 'undetermined';

type CheckboxProps = {
  /**
   * State of the Checkbox.
   */
  status: CheckboxStatus;

  /**
   * Displays the value of the input, but does not allow changes.
   */
  readOnly?: boolean;

  /**
   * Provide a description of the Checkbox, the label appears on the right of the checkboxes.
   */
  label?: string;

  /**
   * The handler called when clicking on Checkbox.
   */
  onChange?: (value: CheckboxStatus) => void;
};

/**
 * The checkboxes are applied when users can select all, several, or none of the options from a given list.
 */
const Checkbox = React.forwardRef<HTMLDivElement, CheckboxProps>(
  (
    {label, status, onChange, readOnly = false}: CheckboxProps,
    forwardedRef: Ref<HTMLDivElement>
  ): React.ReactElement => {
    if (undefined === onChange && false === readOnly) {
      throw new Error('A Checkbox element expect an onChange attribute if not readOnly');
    }

    const [id] = useState<string>(`checkbox_${uuid()}`);

    const checked = 'checked' === status;
    const undetermined = 'undetermined' === status;

    const handleChange = () => {
      if (!onChange || readOnly) return;

      switch (status) {
        case 'checked':
          onChange('unchecked');
          break;
        case 'undetermined':
        case 'unchecked':
          onChange('checked');
          break;
      }
    };
    const ref = useShortcut(Key.Space, handleChange);

    return (
      <Container onClick={handleChange} ref={forwardedRef}>
        <CheckboxContainer
          checked={checked || undetermined}
          readOnly={readOnly}
          role="checkbox"
          ref={ref}
          aria-checked={getAriaChecked(status)}
          tabIndex={readOnly ? -1 : 0}
          aria-labelledby={id}
        >
          {undetermined ? <PartialCheckIcon height={20} width={20} /> : <TickIcon height={20} width={20} />}
        </CheckboxContainer>
        {label ? (
          <LabelContainer id={id} readOnly={readOnly}>
            {label}
          </LabelContainer>
        ) : null}
      </Container>
    );
  }
);

export {Checkbox};
