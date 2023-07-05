import React from 'react';
import styled from 'styled-components';
import {IconProps} from '../../../icons';
import {Override} from '../../../shared';
import {AkeneoThemedProps, getColor, getFontSize} from '../../../theme';
import {Tag} from '../../Tags/Tags';

const Link = styled.a<{active: boolean; disabled: boolean} & AkeneoThemedProps>`
  align-items: center;
  box-sizing: border-box;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  display: flex;
  flex-direction: column;
  font-size: ${getFontSize('small')};
  height: 70px;
  justify-content: center;
  line-height: 1.15;
  margin: 0;
  outline-style: none;
  padding: 7px;
  position: relative;
  text-decoration: none;
  width: 80px;

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

const Label = styled.span`
  margin-top: 7px;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
`;

const TagContainer = styled.div`
  overflow: hidden;
  position: absolute;
  right: 0;
  top: 7px;
  width: 50%;
`;

type MainNavigationItemProps = Override<
  React.AnchorHTMLAttributes<HTMLAnchorElement>,
  {
    /**
     * The Icon to display
     */
    icon: React.ReactElement<IconProps>;

    /**
     * Children are a string label
     */
    children: React.ReactNode;

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
  }
>;

const MainNavigationItem: React.FC<MainNavigationItemProps> = React.forwardRef<HTMLAnchorElement, MainNavigationItemProps>(
  ({children, href, icon, active = false, disabled = false, onClick, ...rest}, forwardedRef) => {
    const handleClick = (event: React.MouseEvent<HTMLAnchorElement>) => {
      if (disabled) {
        event.preventDefault();

        return;
      }

      onClick?.(event);
    };

    let tag: React.ReactElement<typeof Tag> | null = null;
    const taglessChildren = React.Children.map(children, child => {
      if (React.isValidElement(child) && child.type === Tag) {
        if (null === tag) {
          tag = child as any;

          return null;
        }
        throw new Error('You can only provide one component of type Tag.');
      }

      return child;
    });

    return (
      <Link
        ref={forwardedRef}
        href={disabled ? undefined : href}
        active={active}
        disabled={disabled}
        aria-disabled={disabled}
        onClick={handleClick}
        {...rest}
      >
        {React.cloneElement(icon, {size: 24})}
        {tag && <TagContainer>{tag}</TagContainer>}
        <Label>{taglessChildren}</Label>
      </Link>
    );
  }
);

export {MainNavigationItem};
