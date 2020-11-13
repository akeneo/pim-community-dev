import React, {isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

const Step = styled.a<{color: string} & AkeneoThemedProps>`
  text-transform: uppercase;
  color: ${({color}) => getColor(color, 120)};
  text-decoration: none;
`;

Step.displayName = 'Step';

const BreadcrumbContainer = styled.nav<{color: string} & AkeneoThemedProps>`
  ${Step}:last-child {
    color: ${({color}) => getColor(color, 100)};
  }
`;

const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbProps = {
  /**
   * Color of the breadcrumb (grey, brand, yellow, etc)
   */
  color?: string;

  /**
   * Children can only be a `Breadcrumb.Step` elements. Other type of children will not be displayed
   */
  children: ReactElement<React.AnchorHTMLAttributes<HTMLAnchorElement>>;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const Breadcrumb = ({children, color = 'grey', ...rest}: BreadcrumbProps) => {
  const childrenCount = React.Children.count(children);

  // https://www.w3.org/TR/wai-aria-practices-1.1/examples/breadcrumb/index.html
  return (
    <BreadcrumbContainer aria-label="Breadcrumb" color={color} {...rest}>
      {React.Children.map(children, (child, index) => {
        if (!(isValidElement(child) && child.type === Step)) return null;

        const isLastStep = childrenCount - 1 === index;

        return isLastStep ? (
          React.cloneElement(child, {'aria-current': 'page'})
        ) : (
          <>
            {React.cloneElement(child, {color})}
            <Separator aria-hidden="true">/</Separator>
          </>
        );
      })}
    </BreadcrumbContainer>
  );
};

Breadcrumb.Step = Step;

export {Breadcrumb};
