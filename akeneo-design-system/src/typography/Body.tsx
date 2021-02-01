import styled, {css, FlattenInterpolation} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize} from '../theme';

type BodyProps = (
  | {
      size: 'big';
      color?: 'grey';
      gradient?: 120 | 140;
      weight?: 'regular';
    }
  | ({
      size: 'regular';
      weight?: 'regular' | 'semibold';
    } & (
      | {
          color?: 'grey';
          gradient?: 100 | 120 | 140;
        }
      | {
          color?: 'brand';
          gradient?: 140;
        }
    ))
) &
  AkeneoThemedProps;

const getBodyStyle = ({
  size = 'regular',
  color = 'grey',
  weight = 'regular',
  gradient,
}: BodyProps): FlattenInterpolation<any> => {
  if (undefined === gradient) {
    gradient = 'big' === size ? 120 : 'grey' === color ? 100 : 140;
  }

  const fontWeight = 'regular' === weight ? 400 : 600;

  return css`
    color: ${getColor(color, gradient)};
    font-weight: ${fontWeight};
    font-size: ${getFontSize('regular' === size ? 'default' : size)};
  `;
};

const Body = styled.span<BodyProps & AkeneoThemedProps>`
  ${getBodyStyle}
`;

export {Body, getBodyStyle};
