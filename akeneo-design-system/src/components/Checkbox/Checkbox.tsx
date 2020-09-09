import React from 'react';
import styled, {css} from 'styled-components';
import {CheckIcon, PartialCheckIcon} from '../../icons';

const Container = styled.div`
    display: flex;
`;

const CheckboxContainer = styled.div <{ checked: boolean, readOnly: boolean }>`
  background-color: transparent;
  height: 20px;
  width: 20px;
  border: 1px solid ${({theme}) => theme.palette.checkbox.borderColor};
  border-radius: 3px;
  outline: none;

  ${props =>
    (props.checked) && css`
      background-color: ${({theme}) => theme.palette.checkbox.checked.backgroundColor};
  `}

  ${props =>
    props.checked && props.readOnly && css`
      background-color: ${({theme}) => theme.palette.checkbox.checkedAndDisabled.backgroundColor};
      border-color: ${({theme}) => theme.palette.checkbox.checkedAndDisabled.borderColor};
  `}

  ${props =>
    !props.checked && props.readOnly && css`
      background-color: ${({theme}) => theme.palette.checkbox.disabled.backgroundColor};
      border-color: ${({theme}) => theme.palette.checkbox.disabled.borderColor};
  `}
`;

const LabelContainer = styled.div <{ readOnly: boolean }>`
  color: ${({theme}) => theme.palette.formLabel.color};
  font-weight: 400;
  font-size: 15px;
  padding-left: 10px;

  ${props =>
    props.readOnly && css`
      color: ${({theme}) => theme.palette.formLabel.disabled.color};
  `}
`;

type CheckboxProps = {
  /**
   * State of the Checkbox
   */
  checked: boolean,

  /**
   * Displays the value of the input, but does not allow changes.s
   */
  readOnly?: boolean,

  /**
   * The undetermined state comes into play when the checkbox contains a sublist of selections,
   * some of which are selected, and others aren't.
   */
  undetermined?: boolean,

  /**
   * Provide a description of the Checkbox, the label appears on the right of the checkboxes.
   */
  label?: string,

  /**
   * The handler called when clicking on Checkbox
   */
  onChange?: (value: boolean) => void,
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
        {undetermined ? (
          <PartialCheckIcon height={20} width={20}/>
        ) : checked ? (
          <CheckIcon height={20} width={20}/>
        ) : null}
      </CheckboxContainer>
      {label ? (
        <LabelContainer readOnly={readOnly}>
          {label}
        </LabelContainer>
      ) : null}
    </Container>
  );
};

export {Checkbox};
