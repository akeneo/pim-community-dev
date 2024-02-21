import React from 'react';
import {IconProps} from './IconProps';

const ArrowUpIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <polyline
        vectorEffect="non-scaling-stroke"
        stroke={color}
        points="2 17.0002962 12 6.99970375 22 17.0002962 22 17.0002962"
      />
    </g>
  </svg>
);

export {ArrowUpIcon};
