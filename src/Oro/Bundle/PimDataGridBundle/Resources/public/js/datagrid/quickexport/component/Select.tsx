import React, {ReactNode, Children, cloneElement, isValidElement} from 'react';
import styled from 'styled-components';
import {OptionProps} from './Option';

const SelectContainer = styled.div<{isVisible: boolean}>`
  display: flex;
  opacity: ${({isVisible}) => (isVisible ? 1 : 0)};

  :not(:first-child) {
    margin-top: 40px;
  }
`;

type SelectProps = {
  children?: ReactNode;
  name: string;
  value?: string | null;
  isVisible?: boolean;
  onChange?: (value: string | null) => void;
};

const Select = ({value, onChange, isVisible, children}: SelectProps) => {
  return (
    <SelectContainer isVisible={!!isVisible}>
      {Children.map(children, child => {
        if (!isValidElement<OptionProps>(child)) {
          return child;
        }

        return cloneElement<OptionProps>(child, {
          isSelected: child.props.value === value,
          onSelect: () => undefined !== onChange && onChange(child.props.value),
        });
      })}
    </SelectContainer>
  );
};

export {Select, SelectProps};
