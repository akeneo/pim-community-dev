import React, {Ref, ReactElement, SyntheticEvent, ReactNode, isValidElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {IconProps} from '../../../icons';
import {Tag} from '../../Tags/Tags';

const Container = styled.button<{active: boolean; disabled: boolean} & AkeneoThemedProps>`
  width: 80px;
  height: 70px;
  margin: 0;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  background-color: transparent;
  outline-style: none;
  text-decoration: none;
  line-height: 1.15;
  white-space: nowrap;
  font-size: ${getFontSize('small')};
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  border: none;
  border-left: 4px solid
    ${({active, disabled}) => {
      return !disabled && active ? getColor('brand', 100) : 'transparent';
    }};

  color: ${({active, disabled}) => {
    return disabled ? getColor('grey', 100) : active ? getColor('brand', 100) : getColor('grey', 120);
  }};
  svg {
    color: ${({active, disabled}) => {
      return disabled ? getColor('grey', 80) : active ? getColor('brand', 100) : getColor('grey', 100);
    }};
  }

  :hover {
    border-color: ${({disabled}) => !disabled && getColor('brand', 100)};
    color: ${({disabled}) => !disabled && getColor('brand', 100)};
    svg {
      color: ${({disabled}) => !disabled && getColor('brand', 100)};
    }
  }

  :focus:not(:active) {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    outline: none;
  }
`;

const Title = styled.p`
  font-size: ${getFontSize('small')};
  margin: 0;
  margin-top: 7px;
`;

const TagContainer = styled.div`
  position: absolute;
  left: 39px;
  top: 7px;
`;

type MainNavigationItemProps = {
  /**
   * The Icon to display
   */
  icon: ReactElement<IconProps>;

  /**
   * Children are a string label
   */
  children?: ReactNode;

  /**
   * Define if the component is active
   */
  active?: boolean;

  /**
   * Define if the component will be displayed as disabled
   */
  disabled?: boolean;

  /**
   * Url to go to if the button is clicked.
   */
  href?: string;

  /**
   * The callback to call when user clicks on the component
   */
  onClick?: (event: SyntheticEvent) => void;
};

const MainNavigationItem = React.forwardRef<HTMLDivElement, MainNavigationItemProps>(
  (
    {icon, onClick, href, active = false, disabled = false, children, ...rest}: MainNavigationItemProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleClick = (event: SyntheticEvent) => {
      if (disabled || undefined === onClick) return;

      onClick(event);
    };

    let tag = null;
    const taglessChildren = React.Children.map(children, child => {
      if (isValidElement(child) && child.type === Tag) {
        tag = child;
        return null;
      }
      return child;
    });

    return (
      <Container
        as={undefined !== href ? 'a' : 'button'}
        disabled={disabled}
        active={active}
        onClick={handleClick}
        href={disabled ? undefined : href}
        ref={forwardedRef}
        {...rest}
      >
        {React.cloneElement(icon, {size: 20})}
        <Title>{taglessChildren}</Title>
        <TagContainer>{tag}</TagContainer>
      </Container>
    );
  }
);

export {MainNavigationItem};
