import styled, {css} from 'styled-components';
import React, {ReactNode, Ref} from 'react';
import {AkeneoThemedProps, getColor} from '../../theme';

const LinkContainer = styled.a<{disabled: boolean} & AkeneoThemedProps>`
  font-weight: 400;
  text-decoration: underline;
  color: ${props => (props.disabled ? getColor('grey100') : getColor('purple100'))};
  cursor: ${props => (props.disabled ? 'not-allowed' : 'pointer')};

  ${props =>
    !props.disabled &&
    css`
      &:hover {
        color: ${getColor('purple120')};
      }

      &:focus {
        border-radius: 0px;
        box-shadow: 0px 0px 0px 2px rgba(74, 144, 226, 0.3);
        outline: none;
      }

      &:active {
        color: ${getColor('purple140')};
      }
    `};
`;

type LinkProps = {
  /**
   * Specify if the control should be disabled, or not
   */
  disabled?: boolean;

  /**
   * Provide the content for the Link
   */
  children: ReactNode;

  /**
   * Provide the `href` attribute for the `<a>` node
   */
  href?: string;
} & React.AnchorHTMLAttributes<HTMLAnchorElement>;

const Link = React.forwardRef<HTMLAnchorElement, LinkProps>(
  (
    {disabled = false, href, children, target, ...rest}: LinkProps,
    forwardedRef: Ref<HTMLAnchorElement>
  ): React.ReactElement => {
    return (
      <LinkContainer
        disabled={disabled}
        ref={forwardedRef}
        rel={target === '__blank' ? 'noopener noreferrer' : ''}
        {...(!disabled ? {href: href} : {})}
        {...rest}
      >
        {children}
      </LinkContainer>
    );
  }
);

export {Link};
