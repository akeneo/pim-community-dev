import React, {ReactNode, cloneElement, isValidElement} from 'react';
import styled from 'styled-components';
import {getFontSize, getColor, AkeneoThemedProps} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbFoxContainer = styled.nav``;
const Item = styled.a<{gradient: number; color: string} & AkeneoThemedProps>`
  color: ${({gradient, color}) => getColor(color, gradient)};
  font-size: ${getFontSize('default')};
  text-transform: uppercase;
  text-decoration: none;
`;

const Sep = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbFoxProps = {
  /**
   * color of items
   */
  color?: 'red' | 'blue' | 'green' | 'yellow';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy
 */
const BreadcrumbFox = ({children, color = 'red', ...rest}: BreadcrumbFoxProps) => {
  const itemCounts = React.Children.count(children);
  const decoratedChildren = React.Children.map(children, (child, i) => {
    if (!isValidElement(child) || child.type !== Item) return null;
    //return cloneElement(child, {color, gradient: i === itemCounts - 1 ? 100 : 120 });
    return i === itemCounts - 1 ? (
      cloneElement(child, {color, gradient: 100, 'aria-current': 'page'})
    ) : (
      <>
        {cloneElement(child, {color, gradient: 120})}
        <Sep aria-hidden>/</Sep>
      </>
    );
  });
  return <BreadcrumbFoxContainer aria-label="Breadcrumb" {...rest}>{decoratedChildren}</BreadcrumbFoxContainer>;
};

BreadcrumbFox.Item = Item;

Item.displayName = 'BreadcrumbFox.Item';

export {BreadcrumbFox};
