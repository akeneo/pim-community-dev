import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';

//TODO be sure to select the appropriate container element here
const MietteDePainContainer = styled.nav``;

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
Miette.displayName = 'Miette';

const Separator = styled.span<MietteProps>`
  margin: 0 0.5rem;
  color: ${({color, gradient}) => getColor(color, gradient)};
  :after {
    content: '/';
  }
`;
Separator.defaultProps = {color: 'grey', gradient: 120};
Separator.displayName = 'Separator';

/**
 * Breadcrumbs are very effective for products and experience
 * with a large amount of content organized in a multi-level hierarchy.
 */
const MietteDePain = ({color = 'grey', children, ...rest}: MietteDePainProps) => {
  return (
    <MietteDePainContainer {...rest} aria-label="Breadcrumb">
      {React.Children.map(children, (child, index) => {
        if (!React.isValidElement(child) || child.type !== Miette) {
          return null;
        }

        const isNotLast = React.Children.count(children) !== index + 1;

        return (
          <>
            {React.cloneElement(child, {
              color,
              gradient: isNotLast ? 120 : 100,
              'aria-current': isNotLast ? undefined : 'page',
            })}
            {isNotLast && <Separator gradient={100} color={color} aria-hidden={true} />}
          </>
        );
      })}
    </MietteDePainContainer>
  );
};

MietteDePain.Miette = Miette;
MietteDePain.Separator = Separator;

export {MietteDePain};
