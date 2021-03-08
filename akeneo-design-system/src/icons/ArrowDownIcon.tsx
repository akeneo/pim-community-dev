import React from 'react';
import {IconProps} from './IconProps';

const ArrowDownIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <polyline vectorEffect="non-scaling-stroke" stroke={color} points="2 6.99970375 12 17.0002962 22 6.99970375" />
    </g>
  </svg>
);

export {ArrowDownIcon};
