import React, {Ref, SyntheticEvent} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Tag} from '../Tags/Tags';

const SubNavigationItemContainer = styled.button<{active: boolean; disabled: boolean} & AkeneoThemedProps>`
  width: 280px;
  height: 38px;
  display: flex;
  background: none;
  outline: none;
  text-decoration: none;
  border: none;
  box-sizing: border-box;
  line-height: 1.15;
  margin: 0;
  padding: 10px 30px;
  text-align: left;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  color: ${({active, disabled}) => {
    return disabled ? getColor('grey', 100) : active ? getColor('brand', 100) : getColor('grey', 140);
  }};

  :hover {
    color: ${({disabled}) => !disabled && getColor('brand', 100)};
  }

  :focus:not(:active) {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;
const Title = styled.span`
  font-size: ${getFontSize('big')};
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  margin-right: 10px;
`;

type SubNavigationItemProps = {
  /**
   * The title of the button
   */
  title: string;

  /**
   * Optional tag to display
   */
  tag?: string;

  /**
   * The tint/color to apply on the tag;
   */
  tagTint?: string;

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

  /**
   * Url to go to if the button is clicked.
   */
  href?: string;
};

const SubNavigationItem = React.forwardRef<HTMLDivElement, SubNavigationItemProps>(
  (
    {title, tag, tagTint = 'blue', active = false, disabled = false, onClick, href, ...rest}: SubNavigationItemProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const handleClick = (event: SyntheticEvent) => {
      if (disabled || undefined === onClick) return;

      onClick(event);
    };
    return (
      <SubNavigationItemContainer
        as={undefined !== href ? 'a' : 'button'}
        disabled={disabled}
        active={active}
        onClick={handleClick}
        href={disabled ? undefined : href}
        ref={forwardedRef}
        {...rest}
      >
        <Title>{title}</Title>
        {tag && <Tag tint={tagTint}>{tag}</Tag>}
      </SubNavigationItemContainer>
    );
  }
);

export {SubNavigationItem};
