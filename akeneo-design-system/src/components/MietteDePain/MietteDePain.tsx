import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const MietteDePainContainer = styled.div<{level: string}>``;

type MietteDePainProps = {
  /**
   * Color of the Miette.
   */
  color?: 'grey' | 'green' | 'blue' | 'yellow' | 'brand';

  /**
   * TODO.
   */
  children?: ReactNode;
};

type MietteProps = AkeneoThemedProps & {color: string; gradient: number};

const Miette = styled.a<MietteProps>`
  text-transform: uppercase;
  color: ${({color, gradient}) => getColor(color, gradient)};

  &:hover {
    color: ${getColor('brand', 120)};
  }
`;
Miette.defaultProps = {color: 'grey', gradient: 120};

const Separator = styled.span<MietteProps>`
  margin: 0 0.5rem;
  color: ${({color, gradient}) => getColor(color, gradient)};
  :after {
    content: '/';
  }
`;
Separator.defaultProps = {color: 'grey', gradient: 120};

/**
 * Breadcrumbs are very effective for products and experience with a large amount of content organized in a multi-level hierarchy.
 */
const MietteDePain = ({level = 'primary', children, ...rest}: MietteDePainProps) => {
  return (
    <MietteDePainContainer level={level} {...rest}>
      {children}
    </MietteDePainContainer>
  );
};

MietteDePain.Miette = Miette;
MietteDePain.Separator = Separator;

export {MietteDePain};
