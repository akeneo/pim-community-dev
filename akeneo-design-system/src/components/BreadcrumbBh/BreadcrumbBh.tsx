import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbBhContainer = styled.div<{level: string}>``;
const PathItem = styled.a`
  font-size: 13px;
  font-weight: 400;
  text-transform: uppercase;
  color: ${getColor('grey', 120)};
  text-decoration: none;
`;
const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbBhProps = {
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
const BreadcrumbBh = React.forwardRef<HTMLDivElement, BreadcrumbBhProps>(
  ({level = 'primary', children, ...rest}: BreadcrumbBhProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <BreadcrumbBhContainer level={level} ref={forwardedRef} {...rest}>
        {children}
      </BreadcrumbBhContainer>
    );
  }
);

export {BreadcrumbBh, PathItem, Separator};
