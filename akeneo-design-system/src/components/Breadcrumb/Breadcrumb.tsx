import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

type Color = 'blue' | 'red' | 'green' | 'purple';

//TODO be sure to select the appropriate container element here
const BreadcrumbContainer = styled.div<{color: Color}>``;

type BreadcrumbProps = {
  /**
   * Define the color of the breadcrumb
   */
  color: Color;

  /**
   * Items of the breadcrumb (only accepts Item components)
   */
  children?: ReactNode;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const Breadcrumb = React.forwardRef<HTMLDivElement, BreadcrumbProps>(
  ({color = 'blue', children, ...rest}: BreadcrumbProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <BreadcrumbContainer color={color} ref={forwardedRef} {...rest}>
        {children}
      </BreadcrumbContainer>
    );
  }
);

const Item = styled.a<{color: string, gradient: number} & AkeneoThemedProps>`
  text-transform: uppercase;
  text-decoration: none;
  color: ${({color, gradient}) => getColor(color, gradient)}
`;

const Separator = styled.span`
  margin: 0 0.5rem;
`;

export {Breadcrumb, Item, Separator};
