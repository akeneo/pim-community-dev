import React, {isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {getColor, PlaceholderStyle} from '../../theme';
import {Link, LinkProps} from '../../components/Link/Link';

const Step = styled(Link)`
  text-transform: uppercase;
  text-decoration: none;
  color: ${getColor('grey', 120)};

  :last-child {
    color: ${getColor('grey', 100)};
    cursor: initial;
  }
`;
Step.displayName = 'Breadcrumb.Step';

const Separator = styled.span`
  margin: 0 0.5rem;
`;

type BreadcrumbProps = {
  /**
   * Children can only be `Breadcrumb.Step` elements. Other type of children will not be displayed.
   */
  children: ReactElement<LinkProps> | ReactElement<LinkProps>[];
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const Breadcrumb = ({children, ...rest}: BreadcrumbProps) => {
  const childrenCount = React.Children.count(children);

  // https://www.w3.org/TR/wai-aria-practices-1.1/examples/breadcrumb/index.html
  return (
    <nav aria-label="Breadcrumb" {...rest}>
      {React.Children.map(children, (child, index) => {
        if (!isValidElement<LinkProps>(child)) {
          throw new Error('Breadcrumb only accepts `Breacrumb.Step` elements as children');
        }

        const isLastStep = childrenCount - 1 === index;

        return isLastStep ? (
          React.cloneElement(child, {'aria-current': 'page', disabled: true})
        ) : (
          <>
            {child}
            <Separator aria-hidden={true}>/</Separator>
          </>
        );
      })}
    </nav>
  );
};

Object.assign(Step, {
  Skeleton: styled(Step)`
    ${PlaceholderStyle}
  `,
});

Breadcrumb.Step = Step;

export {Breadcrumb};
