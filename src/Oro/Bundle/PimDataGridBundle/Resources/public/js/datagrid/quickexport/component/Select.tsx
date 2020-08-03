import React, {ReactNode, Children, cloneElement, isValidElement} from 'react';
import styled from 'styled-components';
import {OptionProps, Option} from './Option';

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

const Select = ({name, value, onChange, isVisible, children}: SelectProps) => {
  return (
    <SelectContainer role={`${name}-select`} isVisible={!!isVisible} data-visible={!!isVisible}>
      {Children.map(children, child => {
        if (!isValidElement<OptionProps>(child) || child.type !== Option) {
          return child;
        }

        return cloneElement<OptionProps>(child, {
          isSelected: child.props.value === value,
          isDisabled: !isVisible,
          onSelect: () => undefined !== onChange && onChange(child.props.value),
        });
      })}
    </SelectContainer>
  );
};

export {Select, SelectProps};
