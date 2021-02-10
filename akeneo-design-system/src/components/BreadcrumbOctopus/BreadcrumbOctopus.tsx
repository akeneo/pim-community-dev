import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';

const BreadcrumbOctopusContainer = styled.nav``;

const Item = styled.a<{gradient: number; color: string} & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  text-transform: uppercase;
  color: ${props => {
    return getColor(props.color, props.gradient);
  }};
  text-decoration: none;
`;

const Separator = styled.span`
  ::before {
    content: '/';
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
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const BreadcrumbOctopus = ({color = 'blue', children, ...rest}: BreadcrumbOctopusProps) => {
  const decoratedChildren = React.Children.map(children, (child, index) => {
    if (!(React.isValidElement(child) && child.type === Item)) {
      throw new Error('pas bien');
    }

    const isLast = React.Children.count(children) === index + 1;

    return isLast ? (
      React.cloneElement(child, {color, gradient: 100, 'aria-current': 'page'})
    ) : (
      <>
        {React.cloneElement(child, {color, gradient: 120})}
        <Separator />
      </>
    );
  });

  return (
    <BreadcrumbOctopusContainer aria-label="Breadcrumb" {...rest}>
      {decoratedChildren}
    </BreadcrumbOctopusContainer>
  );
};

BreadcrumbOctopus.Item = Item;

export {BreadcrumbOctopus, Separator};
