import React, {Ref, ReactNode, Fragment} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbBhContainer = styled.nav``;
const PathItem = styled.a<{color: BreadCrumbColor; gradient: number} & AkeneoThemedProps>`
  font-size: 13px;
  font-weight: 400;
  text-transform: uppercase;
  color: ${props => getColor(props.color, props.gradient)};
  text-decoration: none;
`;
PathItem.displayName = 'breadcrum.pathItem';
const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadCrumbColor = 'brand' | 'red' | 'grey' | 'blue';

type BreadcrumbBhProps = {
  /**
   * color of the breadcrumb component
   */
  color?: BreadCrumbColor;
  separatorChar?: string;

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const BreadcrumbBh = ({separatorChar = '/', children, color = 'grey', ...rest}: BreadcrumbBhProps) => {
  return (
    <BreadcrumbBhContainer aria-label="Breadcrumb" {...rest}>
      {React.Children.map(children, (child, index) => {
        if (!React.isValidElement(child)) return child;
        return React.Children.count(children) - 1 === index ? (
          React.cloneElement(child, {color, gradient: 100, 'aria-current': 'page', href: undefined})
        ) : (
          <>
            {React.cloneElement(child, {color, gradient: 120})}
            <Separator>{separatorChar}</Separator>
          </>
        );
      })}
    </BreadcrumbBhContainer>
  );
};

BreadcrumbBh.Item = PathItem;

export {BreadcrumbBh, Separator};
