import React, {isValidElement, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbTrainingContainer = styled.nav<{color: string}>``;

const Level = styled.a<{color: string; gradient: number} & AkeneoThemedProps>`
  color: ${props => getColor(props.color, props.gradient)};
  text-transform: uppercase;
  text-decoration: none;
`;
const Separator = styled.span<AkeneoThemedProps>`
  margin: 0 0.5rem;
  color: ${() => getColor('grey', 120)};
`;

type BreadcrumbTrainingProps = {
  /**
   * Color of the breadcrumb
   */
  color?: 'blue' | 'red' | 'yellow';

  /**
   * Children of the breadcrumb (only accept Level elements)
   */
  children?: ReactNode;
};

/**
 * Breadcrumbs are an important navigation component that shows content hierarchy.
 */
const BreadcrumbTraining = ({color = 'blue', children, ...rest}: BreadcrumbTrainingProps) => {
  const decoratedChildren = React.Children.map(children, (child, index) => {
    if (!(isValidElement(child) && Level === child.type)) {
      return null;
    }

    const isLast = React.Children.count(children) === index + 1;
    if (isLast) {
      return React.cloneElement(child, {color, gradient: 100, 'aria-current': 'page'});
    }

    return (
      <>
        {React.cloneElement(child, {color, gradient: 120})}
        <Separator aria-hidden={true}>/</Separator>
      </>
    );
  });

  return (
    <BreadcrumbTrainingContainer color={color} {...rest} aria-label="Breadcrumb">
      {decoratedChildren}
    </BreadcrumbTrainingContainer>
  );
};

BreadcrumbTraining.Level = Level;
BreadcrumbTraining.Separator = Separator;

export {BreadcrumbTraining};
