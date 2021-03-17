import React, {Ref, ReactNode, cloneElement, isValidElement} from 'react';
import styled from 'styled-components';
import {getFontSize, getColor, AkeneoThemeProps} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbFoxContainer = styled.div<{level: string}>``;
const Item = styled.a<{gradient: number, color: string} & AkeneoThemeProps>`
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
  color: 'red' | 'blue' | 'green' | 'yellow';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const BreadcrumbFox = React.forwardRef<HTMLDivElement, BreadcrumbFoxProps>(
  ({children, color = 'red', ...rest}: BreadcrumbFoxProps, forwardedRef: Ref<HTMLDivElement>) => {
    const itemCounts = React.Children.count(children);
    const decoratedChildren = React.Children.map(children, (child, i) => {
            if (!isValidElement(child) || child.type !== Item) return null;
            //return cloneElement(child, {color, gradient: i === itemCounts - 1 ? 100 : 120 });
            return i === itemCounts - 1 ? cloneElement(child, {color, gradient: 100}) : <>
                {cloneElement(child, {color, gradient: 120})}
                <Sep>/</Sep>
            </>
        });
    return (
      <BreadcrumbFoxContainer ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </BreadcrumbFoxContainer>
    );
  }
);

BreadcrumbFox.Item = Item;

export {BreadcrumbFox};
