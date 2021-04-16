import React from 'react';
import {IconProps} from './IconProps';

const BarChartsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M21.5 19.5h-19 1V12h4v7.5H10V4h4v15.5h2.5V9h4v10.5"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {BarChartsIcon};
