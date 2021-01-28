import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps, getFontSize} from '../theme';

const getCaptionStyle = () => {
  return css`
    color: ${getColor('grey', 120)};
    font-weight: 400;
    font-size: ${getFontSize('small')};
  `;
};

const Caption = styled.span<AkeneoThemedProps>`
  ${getCaptionStyle}
`;

export {Caption, getCaptionStyle}
