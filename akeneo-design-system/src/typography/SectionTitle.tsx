import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize} from '../theme';

type SectionTitleProps =
  ({
      size: 'regular';
      color: 'brand' | 'grey';
    }
  | {
      size: 'small' | 'big';
      color: 'grey';
    }) & AkeneoThemedProps;

const getSectionTitleStyle = ({size = 'regular', color = 'grey'}: SectionTitleProps) => () => {
  const gradient = 'small' === size ? 100 : 140;
  const sizeName = 'regular' === size ? 'default' : size

  return css`
    text-transform: uppercase;
    color: ${getColor(color, gradient)};
    font-weight: 400;
    font-size: ${getFontSize(sizeName)}
  `;
};

const SectionTitle = styled.span<SectionTitleProps & AkeneoThemedProps>`
  ${getSectionTitleStyle}
`;

export {SectionTitle, getSectionTitleStyle}
