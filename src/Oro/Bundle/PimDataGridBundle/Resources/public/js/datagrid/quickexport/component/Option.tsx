import React, {ReactNode, isValidElement, Children, cloneElement, ButtonHTMLAttributes} from 'react';
import styled from 'styled-components';
import {useAkeneoTheme, IconProps, AkeneoThemedProps} from '@akeneo-pim-community/shared';

const OptionContainer = styled.button<{isSelected: boolean; withIcon: boolean}>`
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
  cursor: ${({onClick}) => (onClick ? 'pointer' : 'default')};

  :not(:first-child) {
    margin-left: 20px;
  }
`;

type OptionProps = {
  value: string;
  isSelected?: boolean;
  isDisabled?: boolean;
  children?: ReactNode;
  onSelect?: () => void;
} & ButtonHTMLAttributes<HTMLButtonElement>;

const Option = ({isSelected, children, onSelect, isDisabled, title}: OptionProps) => {
  const theme = useAkeneoTheme();
  const withIcon = Children.toArray(children).some((child: ReactNode) => isValidElement<IconProps>(child));

  return (
    <OptionContainer
      withIcon={withIcon}
      data-selected={!!isSelected}
      isSelected={!!isSelected}
      onClick={isDisabled ? undefined : onSelect}
      title={title}
    >
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
