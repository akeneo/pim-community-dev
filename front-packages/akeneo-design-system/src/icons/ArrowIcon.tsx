import React from 'react';
import {IconProps} from './IconProps';

const ArrowIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      vectorEffect="non-scaling-stroke"
      d="M21 19H3V2m15 14l3 3-3 3"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {ArrowIcon};
