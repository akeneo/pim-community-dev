import React, {Ref, ReactNode, ReactElement, forwardRef} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from "../../theme";

//TODO be sure to select the appropriate container element here
const BreadcrumbOctopusContainer = styled.div<{level: string}>``;
const Item = styled.span<{gradient:number, color: string} & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  text-transform: uppercase;
  color: ${(props) => {
      return getColor(props.color, props.gradient)
  }};
`;
const Separator = styled.span`
  ::before{
    content:"/";
    margin: 0 0.5rem;
    color: ${getColor('grey', 100)};
  }
`;

type BreadcrumbOctopusProps = {
  /**
   *      The color of the breadcrumb
   */
  color?: 'green' | 'blue' | 'red';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const BreadcrumbOctopus = forwardRef<HTMLDivElement, BreadcrumbOctopusProps>(
  ({color = 'blue', children, ...rest}: BreadcrumbOctopusProps, forwardedRef: Ref<HTMLDivElement>) => {
    const decoratedChildren = React.Children.map(children, (child, index) => {
        if (!(React.isValidElement(child) && child.type === Item)) {
            throw new Error('pas bien');
        }

        const isLast = React.Children.count(children) === index + 1;

        return isLast ? React.cloneElement(child, {color, gradient: 100}) : (
            <>
                {React.cloneElement(child, {color, gradient: 120})}
                <Separator/>
            </>
        );
    })

    return (
      <BreadcrumbOctopusContainer ref={forwardedRef} {...rest}>
        {decoratedChildren}
      </BreadcrumbOctopusContainer>
    );
  }
);

export {BreadcrumbOctopus, Item, Separator};
