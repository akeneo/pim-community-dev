import React from 'react';
import {IconProps} from './IconProps';

export const ArrowSimpleUpIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M12,2 L12,21 L12,2 Z M18,15 L12,22 L6,15 L6,15"
        stroke={color}
        transform="translate(12.000000, 12.000000) rotate(-180.000000) translate(-12.000000, -12.000000) "
      />
    </g>
  </svg>
);
