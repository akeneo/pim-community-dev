import React, {Children, cloneElement, Fragment, isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';
import {Link, LinkProps} from '../../components/Link/Link';

const Step = styled(Link)`
  text-transform: uppercase;
  text-decoration: none;
  color: ${getColor('grey', 120)};
`;

Step.displayName = 'Breadcrumb.Step';

const BreadcrumbContainer = styled.nav`
  ${Step}:last-child {
    color: ${getColor('grey', 100)};
    cursor: initial;
  }
`;

const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbChild = ReactElement<LinkProps> | null | boolean | undefined | BreadcrumbChild[];

type BreadcrumbProps = {
  /**
   * Children can only be `Breadcrumb.Step` elements. Other type of children will not be displayed.
   */
  children: BreadcrumbChild;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const Breadcrumb = ({children, ...rest}: BreadcrumbProps) => {
  const validChildren = Children.toArray(children).filter(isValidElement);

  // https://www.w3.org/TR/wai-aria-practices-1.1/examples/breadcrumb/index.html
  return (
    <BreadcrumbContainer aria-label="Breadcrumb" {...rest}>
      {validChildren.map((child, index) => {
        if (!(isValidElement<LinkProps>(child) && child.type === Step)) {
          throw new Error('Breadcrumb only accepts `Breacrumb.Step` elements as children');
        }

        const isLastStep = validChildren.length - 1 === index;

        return isLastStep ? (
          cloneElement(child, {'aria-current': 'page', disabled: true})
        ) : (
          <Fragment key={index}>
            {child}
            <Separator aria-hidden={true}>/</Separator>
          </Fragment>
        );
      })}
    </BreadcrumbContainer>
  );
};

Breadcrumb.Step = Step;

export {Breadcrumb};
