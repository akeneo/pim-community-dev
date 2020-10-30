import {FormEvent} from 'react';
import * as React from 'react';
import styled, {css} from 'styled-components';
import {TickIcon} from '../icons';

const Tick = styled(TickIcon)`
  animation: uncheckTick 0.2s ease-in forwards;
  opacity: 0;
  stroke-dasharray: 17px;
  stroke-dashoffset: 0;
  transition-delay: 0.2s;
  transition: opacity 0.1s ease-out;
`;

const Container = styled.div<{checked: boolean}>`
  background-color: ${props => (props.checked ? props.theme.color.blue100 : props.theme.color.grey60)};
  border-radius: 2px;
  border: 1px solid ${props => (props.checked ? props.theme.color.blue120 : props.theme.color.grey80)};
  height: 18px;
  margin-right: 5px;
  outline: none;
  transition: background-color 0.2s ease-out;
  width: 18px;

  ${props =>
    props.checked &&
    css`
      &:focus {
        border: 1px solid ${props => props.theme.color.blue140};
      }
      ${Tick} {
        animation-delay: 0.2s;
        animation: checkTick 0.2s ease-out forwards;
        stroke-dashoffset: 17px;
        opacity: 1;
        transition-delay: 0s;
      }
    `}
`;

const HiddenInput = styled.input`
  visibility: hidden;
`;

const Checkbox = ({
  value,
  onChange,
  id = '',
  readOnly = false,
  className = '',
}: {
  value: boolean;
  id?: string;
  onChange?: (value: boolean) => void;
  readOnly?: boolean;
  className?: string;
}) => {
  if (undefined === onChange && false === readOnly) {
    throw new Error(`A Checkbox element expect a onChange attribute if not readOnly`);
  }

  return (
    <Container checked={value} className={`${readOnly ? 'AknCheckbox--disabled' : ''} ${className}`}>
      <Tick />
      <HiddenInput
        checked={value}
        type="checkbox"
        id={id}
        value={value.toString()}
        onChange={(e: FormEvent<HTMLInputElement>) => onChange && !readOnly && onChange(e.currentTarget.checked)}
        readOnly={readOnly}
      />
    </Container>
  );
};

export {Checkbox};
