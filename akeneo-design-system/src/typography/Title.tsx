import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../theme';

const getTitleSizeStyle = (size: 'small' | 'regular' | 'big') => {
  switch (size) {
    case 'small':
      return css`
        font-size: 15px;
        font-weight: 700;
      `;
    case 'regular':
      return css`
        font-size: 17px;
        font-weight: 600;
      `;
    case 'big':
      return css`
        font-size: 28px;
        font-weight: 400;
      `;
  }
};

type TitleProps = {
  size: 'small' | 'regular' | 'big';
  color: 'brand' | 'grey';
};

const getTitleStyle = ({size, color}: TitleProps) => () => {
  const gradient = 'brand' === color ? 100 : 140;

  return css`
    color: ${getColor(color, gradient)};
    ${getTitleSizeStyle(size)}
  `;
};

const Title = styled.span<{color: 'grey' | 'brand'; size: 'small' | 'regular' | 'big'} & AkeneoThemedProps>`
  ${getTitleStyle}
`;

export {Title, getTitleStyle};
