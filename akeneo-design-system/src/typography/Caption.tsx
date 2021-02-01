import styled, {css, FlattenInterpolation} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize} from '../theme';

const getCaptionStyle = (): FlattenInterpolation<any> => {
  return css`
    color: ${getColor('grey', 120)};
    font-weight: 400;
    font-size: ${getFontSize('small')};
  `;
};

const Caption = styled.span<AkeneoThemedProps>`
  ${getCaptionStyle}
`;

export {Caption, getCaptionStyle};
