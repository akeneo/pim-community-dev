import React from 'react';
import {IconProps} from './IconProps';

const ListIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <path
        d="M3.5 17a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0-6.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0-6.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"
        fill={color}
      />
      <path d="M7 18.5h13.5M7 12h13.5M7 5.5h13.5" stroke={color} strokeLinecap="round" strokeLinejoin="round" />
    </g>
  </svg>
);

export {ListIcon};
