import React from 'react';
import {IconProps} from './IconProps';

const IdIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M15 6h6a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V7a1 1 0 011-1h6m2.5-1.5h1a1 1 0 011 1v2a1 1 0 01-1 1h-1a1 1 0 01-1-1v-2a1 1 0 011-1zM13 16h6m-6-2h4.5M13 12h6M7.5 13a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.5 3.5a2.5 2.5 0 10-5 0"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {IdIcon};
