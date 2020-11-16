import React, {Ref, isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

type Color = 'blue' | 'red' | 'green' | 'purple';

//TODO be sure to select the appropriate container element here
const BreadcrumbContainer = styled.nav``;

const Item = styled.a<{color: string; gradient: number} & AkeneoThemedProps>`
  text-transform: uppercase;
  text-decoration: none;
  color: ${({color, gradient}) => getColor(color, gradient)};
`;

type BreadcrumbProps = {
  /**
   * Define the color of the breadcrumb
   */
  color?: Color;

  /**
   * Items of the breadcrumb (only accepts Item components)
   */
  children:
    | ReactElement<React.AnchorHTMLAttributes<HTMLAnchorElement>>
    | ReactElement<React.AnchorHTMLAttributes<HTMLAnchorElement>>[];
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const Breadcrumb = React.forwardRef<HTMLDivElement, BreadcrumbProps>(
  ({color = 'blue', children, ...rest}: BreadcrumbProps, forwardedRef: Ref<HTMLDivElement>) => {
    const decoratedChildren = React.Children.map(children, (child, index) => {
      if (!(isValidElement(child) && child.type === Item)) {
        return null;
      }
      const isLast = React.Children.count(children) - 1 === index;

      return (
        <>
          {React.cloneElement<any>(child, {
            color,
            gradient: isLast ? 100 : 120,
            'aria-current': isLast ? 'page' : undefined,
          })}
          {!isLast && <Separator aria-hidden={true}>/</Separator>}
        </>
      );
    });

    return (
      <BreadcrumbContainer ref={forwardedRef} aria-label="Breadcrumb" {...rest}>
        {decoratedChildren}
      </BreadcrumbContainer>
    );
  }
);

const Separator = styled.span`
  margin: 0 0.5rem;
`;

export {Breadcrumb, Item, Separator};
