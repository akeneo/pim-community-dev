import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize, getFontWeight} from '../theme';

type BodyProps =
  | {
      size: 'big';
      color: 'grey';
      gradient: 140 | 120;
      weight: 'regular';
    }
  | ({
      size: 'regular';
      weight: 'regular' | 'semibold' | 'bold';
    } & (
      | {
          color: 'grey';
          gradient: 100 | 120 | 140;
        }
      | {
          color: 'brand';
          gradient: 140;
        }
    ));

const getBodyStyle = ({size, color, gradient, weight}: BodyProps) => () => {
  const fontWeight = getFontWeight(weight);

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
