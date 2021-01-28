import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize} from '../theme';

type BodyProps =
  ({
      size: 'big';
      color: 'grey';
      gradient: 140 | 120;
      weight: 400;
    }
  | ({
      size: 'regular';
      weight: 'regular' | 'semibold';
    } & (
      | {
          color: 'grey';
          gradient: 100 | 120 | 140;
        }
      | {
          color: 'brand';
          gradient: 140;
        }
    ))) & AkeneoThemedProps;

const getBodyStyle = ({size = 'regular', color = 'grey', gradient = 140, weight = 'regular'}: BodyProps) => () => {
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

export {Body, getBodyStyle}
