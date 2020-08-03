import React, {ReactNode, isValidElement, Children, cloneElement} from 'react';
import styled from 'styled-components';
import {useAkeneoTheme, IconProps, AkeneoThemedProps} from '@akeneo-pim-community/shared';

const OptionContainer = styled.div<{isSelected: boolean; withIcon: boolean}>`
  width: 128px;
  padding: ${({withIcon}) => (withIcon ? 24 : 12)}px 0;
  height: ${({withIcon}) => (withIcon ? '128px' : 'auto')};
  justify-content: space-around;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid;
  border-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : theme.color.grey80};
  background-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue20 : theme.color.white};
  color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : 'inherit'};
  cursor: pointer;

  :not(:first-child) {
    margin-left: 20px;
  }
`;

type OptionProps = {
  value: string;
  isSelected?: boolean;
  children?: ReactNode;
  onSelect?: () => void;
};

const Option = ({isSelected, children, onSelect}: OptionProps) => {
  const theme = useAkeneoTheme();
  const withIcon = Children.toArray(children).some((child: ReactNode) => isValidElement<IconProps>(child));

  return (
    <OptionContainer withIcon={withIcon} isSelected={!!isSelected} onClick={onSelect}>
      {Children.map(children, child => {
        if (!isValidElement<IconProps>(child)) {
          return child;
        }

        return cloneElement<IconProps>(child, {
          color: isSelected ? theme.color.blue100 : child.props.color,
        });
      })}
    </OptionContainer>
  );
};

export {Option, OptionProps};
