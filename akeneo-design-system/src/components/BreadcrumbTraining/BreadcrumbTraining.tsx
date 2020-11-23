import React, {isValidElement, ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const BreadcrumbTrainingContainer = styled.div<{ color: string }>``;

const Level = styled.a<{ color: string, gradient: number } & AkeneoThemedProps>`
color:${props => getColor(props.color, props.gradient)};
text-transform: uppercase;
text-decoration: none;
`;
const Separator = styled.span<{ color: string, gradient: number }>`
margin: 0 0.5rem;
`;

type BreadcrumbTrainingProps = {
  /**
   * TODO.
   */
  color?: 'blue' | 'red' | 'yellow';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const BreadcrumbTraining = ({color = 'blue', children, ...rest}: BreadcrumbTrainingProps) => {
  const decoratedChildren = React.Children.map(children, (child) => {
    if (!(isValidElement(child) && Level === child.type)) {
      return null;
    }

    return <>{React.cloneElement(child, {color, gradient: 120})}<Separator>/</Separator></>;
  });

  return (
    <BreadcrumbTrainingContainer color={color} {...rest}>
      {decoratedChildren}
    </BreadcrumbTrainingContainer>
  );
};

BreadcrumbTraining.Level = Level;
BreadcrumbTraining.Separator = Separator;

export {BreadcrumbTraining};
