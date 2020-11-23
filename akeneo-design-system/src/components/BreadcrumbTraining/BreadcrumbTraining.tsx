import React, {ReactNode} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const BreadcrumbTrainingContainer = styled.div<{level: string}>``;
const Level = styled.a<{color: string, gradient: number}>`
color:blue;
`;
const Separator = styled.span<{color: string, gradient: number}>`
margin: 0 0.5rem;
`;

type BreadcrumbTrainingProps = {
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
const BreadcrumbTraining = ({level = 'primary', children, ...rest}: BreadcrumbTrainingProps) => {
    return (
      <BreadcrumbTrainingContainer level={level} {...rest}>
        {children}
      </BreadcrumbTrainingContainer>
    );
};

BreadcrumbTraining.Level = Level;
BreadcrumbTraining.Separator = Separator;

export {BreadcrumbTraining};
