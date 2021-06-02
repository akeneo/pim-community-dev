import React, {Ref, ReactElement, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {IconProps} from '../../icons';

const Container = styled.button<{active: boolean; disabled: boolean} & AkeneoThemedProps>`
  width: 80px;
  height: 70px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  background-color: transparent;
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

const IconContainer = styled.div`
  position: relative;
`;

const Tag = styled.span`
  text-transform: uppercase;
  text-align: center;
  font-size: ${getFontSize('small')};
  color: #3278b7;
  background-color: #f0f7fc;
  border: 1px solid #4ca8e0;
  box-sizing: border-box;
  display: inline-block;

  position: absolute;
  left: 50%;
  top: -40%;
  overflow: hidden;
  max-width: 200px;
  white-space: nowrap;
  text-overflow: ellipsis;
  height: 18px;
  line-height: 18px;
  padding: 0 6px;
`;

type MainNavigationItemProps = {
  /**
   * The Icon to display
   */
  icon: ReactElement<IconProps>;

  /**
   * The title of the button
   */
  title: string;

  /**
   * Optional tag to display
   */
  tag?: string;

  /**
   * Define if the component is active
   */
  active?: boolean;

  /**
   * Define if the component will be displayed as disabled
   */
  disabled?: boolean;

  /**
   * The callback when the user clicks on the component
   */
  onClick?: (event: SyntheticEvent) => void;
};

const MainNavigationItem = React.forwardRef<HTMLDivElement, MainNavigationItemProps>(
  (
    {icon, title, tag, onClick, active = false, disabled = false, ...rest}: MainNavigationItemProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleClick = (event: SyntheticEvent) => {
      if (disabled || undefined === onClick) return;

      onClick(event);
    };

    return (
      <Container ref={forwardedRef} {...rest} disabled={disabled} active={active} onClick={handleClick}>
        <IconContainer>
          {React.cloneElement(icon, {size: 20})}
          {tag && <Tag>{tag}</Tag>}
        </IconContainer>
        <Title>{title}</Title>
      </Container>
    );
  }
);

export {MainNavigationItem};
