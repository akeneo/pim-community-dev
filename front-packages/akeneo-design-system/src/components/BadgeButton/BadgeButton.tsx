import React, {isValidElement, ReactNode, Ref, SyntheticEvent} from 'react';
import {Override} from '../../shared';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Badge} from '../Badge/Badge';
import {LockIcon} from 'icons';

type BadgeButtonProps = Override<
  React.ButtonHTMLAttributes<HTMLButtonElement> & React.AnchorHTMLAttributes<HTMLAnchorElement>,
  {
    /**
     * Function called when the user clicks on the button or hit enter when focused.
     */
    onClick?: (event: SyntheticEvent) => void;

    /**
     * Define is the component is active by default
     */
    isActive?: boolean;

    /**
     * Children of the button.
     */
    children?: ReactNode;
  }
>;

const activeDisplay = css`
  border-color: ${getColor('brand20')};
  background-color: ${getColor('brand20')};
  color: ${getColor('brand120')};
`;

const Container = styled.button<BadgeButtonProps & AkeneoThemedProps & {hasIcon: boolean}>`
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  gap: 10px;
  font-weight: 400;
  font-size: ${getFontSize('big')} !important;
  color: ${({disabled}) => (disabled ? getColor('grey80') : getColor('grey100'))};
  text-transform: capitalize;
  text-decoration: none;
  border-width: 1px;
  border-radius: 4px;
  border-style: solid;
  border-color: ${({disabled}) => (disabled ? getColor('grey80') : getColor('grey100'))};
  background-color: transparent;
  padding: 6px 10px;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  ${({hasIcon}) =>
    hasIcon &&
    css`
      padding-right: 6px;
    `}

  ${({isActive, disabled}) =>
    isActive &&
    !disabled &&
    css`
      ${activeDisplay}
    `}

  &:hover:not([disabled]) {
    ${activeDisplay}
  }
`;

const Label = styled.div`
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
`;

const BadgeButton = React.forwardRef<HTMLButtonElement, BadgeButtonProps>(
  (
    {onClick, isActive = false, children, disabled, href, ...rest}: BadgeButtonProps,
    forwardedRef: Ref<HTMLButtonElement>
  ) => {
    const buttonLabel = React.Children.toArray(children).find(child => typeof child === 'string');
    const badge = React.Children.toArray(children).find(child => isValidElement(child) && child.type === Badge);

    return (
      <Container
        as={undefined !== href ? 'a' : 'button'}
        isActive={isActive}
        onClick={onClick}
        ref={forwardedRef}
        disabled={disabled}
        hasIcon={disabled || badge !== undefined}
        href={disabled ? undefined : href}
        {...rest}
      >
        <Label>{buttonLabel}</Label>
        {disabled ? <LockIcon size={16} /> : badge}
      </Container>
    );
  }
);

export {BadgeButton};
