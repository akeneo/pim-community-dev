import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, Link} from "..";

//TODO be sure to select the appropriate container element here
const BreadcrumbContainer = styled.div<{color: string}>``;

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

const Breadcrumb = ({color, children, ...rest}: BreadcrumbProps) => {
  const decoratedChildren = React.Children.map(children, (child) => {
    if (!(React.isValidElement(child) && child.type === Breadcrumb.Item)) {
      console.error(`Found an element that was not Breadcrumb.Item as a children of Breadcrumb`)

      return null;
    }
    return React.cloneElement(child, {color})
  });

  return (
    <BreadcrumbContainer {...rest}>
      {children}
    </BreadcrumbContainer>
  );
};

Breadcrumb.Item = styled(Link)<{color: string}>`
  color: ${(props) => getColor(props.color, 100)};
`;

export {Breadcrumb};
