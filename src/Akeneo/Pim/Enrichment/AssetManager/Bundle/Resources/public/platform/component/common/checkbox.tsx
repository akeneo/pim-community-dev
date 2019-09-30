import * as React from 'react';
import TickIcon from 'akeneoassetmanager/application/component/app/icon/tick';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

class InvalidArgumentError extends Error {}

type ContainerProps = {
  checked: boolean;
};

const Tick = styled(TickIcon)`
  animation: uncheckTick 0.2s ease-in forwards;
  stroke-dasharray: 17px;
  stroke-dashoffset: 0;
  opacity: 0;
  transition: opacity 0.1s ease-out;
  transition-delay: 0.2s;
`;

const Container = styled.div<ContainerProps>`
  margin-right: 5px;
  width: 18px;
  height: 18px;
  border-radius: 2px;
  background-color: ${(props: ThemedProps<ContainerProps>) =>
    props.checked ? props.theme.color.blue100 : props.theme.color.grey60};
  border: 1px solid
    ${(props: ThemedProps<ContainerProps>) => (props.checked ? props.theme.color.blue120 : props.theme.color.grey80)};
  transition: background-color 0.2s ease-out;

  ${(props: ThemedProps<ContainerProps>) => {
    return props.checked
      ? `
      &:focus {
        border: 1px solid ${(props: ThemedProps<ContainerProps>) => props.theme.color.blue140};
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
        if ([' '].includes(event.key) && !readOnly && onChange) onChange(!value);
        event.preventDefault();
      }}
      onClick={() => {
        if (!readOnly && onChange) onChange(!value);
      }}
    >
      <Tick />
    </Container>
  );
};

export default Checkbox;
