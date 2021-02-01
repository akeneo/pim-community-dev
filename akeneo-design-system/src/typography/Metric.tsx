import styled, {css} from 'styled-components';
import {getColor, AkeneoThemedProps} from '../theme';

type MetricProps =
  | {
      size: 'big';
      color: 'green' | 'yellow' | 'red';
    }
  | {
      size: 'small';
      color: 'brand';
    };

const getMetricStyle = ({size, color}: MetricProps) => () => {
  const fontSize = size === 'small' ? 16 : 22;
  const fontWeight = size === 'small' ? 600 : 700;
  const gradient = ['red', 'brand'].includes(color) ? 100 : 120;

  return css`
    color: ${getColor(color, gradient)};
    font-weight: ${fontWeight};
    font-size: ${fontSize}px;
  `;
};

const Metric = styled.span<MetricProps & AkeneoThemedProps>`
  ${getMetricStyle}
`;

export {Metric, getMetricStyle};
