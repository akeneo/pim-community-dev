import styled, {css} from 'styled-components';
import React, {ReactNode, Ref} from 'react';
import {AkeneoThemedProps, getColor} from '../../theme';

const LinkContainer = styled.a<{disabled: boolean; decorated: boolean} & AkeneoThemedProps>`
  ${({decorated, disabled}) =>
    decorated
      ? css`
          font-weight: 400;
          text-decoration: underline;
          color: ${disabled ? getColor('grey', 100) : getColor('brand', 100)};

          ${!disabled &&
          css`
            &:hover {
              color: ${getColor('brand', 120)};
            }

            &:focus:not(:active) {
              border-radius: 0px;
              box-shadow: 0px 0px 0px 2px rgba(74, 144, 226, 0.3);
              outline: none;
            }

            &:active {
              outline: none;
              color: ${getColor('brand', 140)};
            }
          `};
        `
      : css`
          text-decoration: none;
          box-sizing: border-box;
        `}

  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
`;

type LinkProps = {
  /**
   * Specify if the control should be disabled, or not
   */
  disabled?: boolean;

  /**
   * Define if it should change color or have underline text
   */
  decorated?: boolean;

  /**
   * Provide the content for the Link
   */
  children: ReactNode;

  /**
   * Define where to display the linked URL
   */
  target?: string;

  /**
   * Provide the `href` attribute for the `<a>` node
   */
  href?: string;
} & React.AnchorHTMLAttributes<HTMLAnchorElement>;

/** Link redirect user to another page */
const Link = React.forwardRef<HTMLAnchorElement, LinkProps>(
  (
    {disabled = false, decorated = true, target = '_self', href, children, ...rest}: LinkProps,
    forwardedRef: Ref<HTMLAnchorElement>
  ): React.ReactElement => {
    return (
      <LinkContainer
        disabled={disabled}
        ref={forwardedRef}
        target={target}
        decorated={decorated}
        rel={target === '_blank' ? 'noopener noreferrer' : ''}
        {...(!disabled ? {href: href} : {})}
        {...rest}
      >
        {children}
      </LinkContainer>
    );
  }
);

export {Link};
export type {LinkProps};
