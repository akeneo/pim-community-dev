import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbContainer = styled.nav``;
const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbProps = {
  /**
   * The level of the link
   */
  color: 'green' | 'red' | 'blue';

  /**
   * The links of the breadcrumb
   */
  children?: ReactNode;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy
 */
const Breadcrumb = ({color, children, ...rest}: BreadcrumbProps) => {
  const decoratedChildren = React.Children.map(children, (child, index) => {
    const isLastStep = index === React.Children.count(children) - 1;
    if (!(React.isValidElement(child) && child.type === Breadcrumb.Item)) {
      console.error(`Found an element that was not Breadcrumb.Item as a children of Breadcrumb`);

      return null;
    }

    return isLastStep ? (
      React.cloneElement(child, {color, gradient: 100, 'aria-current': 'page'})
    ) : (
      <>
        {React.cloneElement(child, {color, gradient: 120})}
        <Separator aria-hidden="true">/</Separator>
      </>
    );
  });

  return (
    <BreadcrumbContainer aria-label="Breadcrumb" {...rest}>
      {decoratedChildren}
    </BreadcrumbContainer>
  );
};

Breadcrumb.Item = styled.a<{color: string; gradient: number} & AkeneoThemedProps>`
  color: ${props => getColor(props.color, props.gradient)};
  text-transform: uppercase;
  text-decoration: none;
`;

export {Breadcrumb};
