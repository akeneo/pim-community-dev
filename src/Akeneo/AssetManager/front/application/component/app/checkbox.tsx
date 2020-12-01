import React from 'react';
import styled from 'styled-components';
import Key from 'akeneoassetmanager/tools/key';
import {CheckIcon, AkeneoThemedProps, getColor} from 'akeneo-design-system';

class InvalidArgumentError extends Error {}

type ContainerProps = {
  checked: boolean;
};

const Tick = styled(CheckIcon)`
  animation: uncheckTick 0.2s ease-in forwards;
  stroke-dasharray: 17px;
  stroke-dashoffset: 0;
  opacity: 0;
  transition: opacity 0.1s ease-out;
  transition-delay: 0.2s;
  color: ${getColor('white')};
`;

const Container = styled.div<ContainerProps>`
  outline: none;
  margin-right: 5px;
  width: 18px;
  height: 18px;
  border-radius: 2px;
  background-color: ${(props: AkeneoThemedProps<ContainerProps>) =>
    props.checked ? props.theme.color.blue100 : props.theme.color.grey20};
  border: 1px solid
    ${(props: AkeneoThemedProps<ContainerProps>) =>
      props.checked ? props.theme.color.blue120 : props.theme.color.grey80};
  transition: background-color 0.2s ease-out;

  ${(props: AkeneoThemedProps<ContainerProps>) => {
    /* istanbul ignore next */
    return props.checked
      ? `
      &:focus {
        border: 1px solid ${(props: AkeneoThemedProps<ContainerProps>) => props.theme.color.blue140};
      }
      ${Tick} {
          animation-delay: .2s;
          animation: checkTick .2s ease-out forwards;
          stroke-dashoffset: 17px;
          opacity: 1;
          transition-delay: 0s;
      }
    `
      : '';
  }}
`;

const Checkbox = ({
  value,
  onChange,
  id = '',
  readOnly = false,
}: {
  value: boolean;
  id?: string;
  onChange?: (value: boolean) => void;
  readOnly?: boolean;
}) => {
  if (undefined === onChange && false === readOnly) {
    throw new InvalidArgumentError(`A Checkbox element expect a onChange attribute if not readOnly`);
  }

  return (
    <Container
      checked={value}
      className={`${readOnly ? 'AknCheckbox--disabled' : ''}`}
      data-checked={value ? 'true' : 'false'}
      tabIndex={readOnly ? -1 : 0}
      id={id}
      role="checkbox"
      aria-checked={value ? 'true' : 'false'}
      onKeyPress={(event: React.KeyboardEvent<HTMLSpanElement>) => {
        if ([Key.Space].includes(event.key as Key) && !readOnly && onChange) onChange(!value);
        event.preventDefault();
      }}
      onClick={() => {
        if (!readOnly) (onChange as (value: boolean) => void)(!value);
      }}
    >
      <Tick size={18} />
    </Container>
  );
};

export default Checkbox;
