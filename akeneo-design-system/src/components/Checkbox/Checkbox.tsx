import React from 'react';
import styled, {css, keyframes} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {CheckIcon, PartialCheckIcon} from '../../icons';

const checkTick = keyframes`
  to {
    stroke-dashoffset: 0;
  }
`;

const uncheckTick = keyframes`
to {
    stroke-dashoffset: 20px;
  }
`;

const Container = styled.div`
  display: flex;
`;

const TickIcon = styled(CheckIcon)`
  animation: ${uncheckTick} 0.2s ease-in forwards;
  opacity: 0;
  stroke-dasharray: 0px;
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
  outline: none;
  background-color: ${getColor('grey20')};

  ${(props) =>
    props.checked &&
    css`
      background-color: ${getColor('blue100')};
      ${TickIcon} {
        animation-delay: 0.2s;
        animation: ${checkTick} 0.2s ease-out forwards;
        stroke-dashoffset: 20px;
        opacity: 1;
        transition-delay: 0s;
      }
    `}

  ${(props) =>
    props.checked &&
    props.readOnly &&
    css`
      background-color: ${getColor('blue20')};
      border-color: ${getColor('blue40')};
    `}

  ${(props) =>
    !props.checked &&
    props.readOnly &&
    css`
      background-color: ${getColor('grey60')};
      border-color: ${getColor('grey100')};
    `}
`;

const LabelContainer = styled.div<{readOnly: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey140')};
  font-weight: 400;
  font-size: 15px;
  padding-left: 10px;

  ${(props) =>
    props.readOnly &&
    css`
      color: ${getColor('grey100')};
    `}
`;

type CheckboxProps = {
  /**
   * State of the Checkbox
   */
  checked: boolean;

  /**
   * Displays the value of the input, but does not allow changes.s
   */
  readOnly?: boolean;

  /**
   * The undetermined state comes into play when the checkbox contains a sublist of selections,
   * some of which are selected, and others aren't.
   */
  undetermined?: boolean;

  /**
   * Provide a description of the Checkbox, the label appears on the right of the checkboxes.
   */
  label?: string;

  /**
   * The handler called when clicking on Checkbox
   */
  onChange?: (value: boolean) => void;
};

/**
 * The checkboxes are applied when users can select all, several, or none of the options from a given list.
 */
const Checkbox = ({label, checked, onChange, undetermined = false, readOnly = false}: CheckboxProps) => {
  if (undefined === onChange && false === readOnly) {
    throw new Error('A Checkbox element expect an onChange attribute if not readOnly');
  }

  const handleChange = () => onChange && !readOnly && onChange(!checked);

  return (
    <Container onClick={handleChange}>
      <CheckboxContainer checked={checked || undetermined} readOnly={readOnly}>
        {undetermined ? <PartialCheckIcon height={20} width={20} /> : <TickIcon height={20} width={20} />}
      </CheckboxContainer>
      {label ? <LabelContainer readOnly={readOnly}>{label}</LabelContainer> : null}
    </Container>
  );
};

export {Checkbox};
