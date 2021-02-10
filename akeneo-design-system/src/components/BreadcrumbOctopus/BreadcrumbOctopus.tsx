import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from "../../theme";

//TODO be sure to select the appropriate container element here
const BreadcrumbOctopusContainer = styled.div<{level: string}>``;
const Item = styled.span<{gradient:number} & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  text-transform: uppercase;
  color: ${(props) => {
      return getColor('grey', props.gradient)
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
   * TODO.
   */
  level?: 'primary' | 'warning' | 'danger';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const BreadcrumbOctopus = React.forwardRef<HTMLDivElement, BreadcrumbOctopusProps>(
  ({level = 'primary', children, ...rest}: BreadcrumbOctopusProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <BreadcrumbOctopusContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </BreadcrumbOctopusContainer>
    );
  }
);

export {BreadcrumbOctopus, Item, Separator};
