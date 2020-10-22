import React from 'react';
import {IconProps} from './IconProps';

const EntityIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M5 2h14a1 1 0 011 1v18a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1zm9.413 9H16a1 1 0 011 1v6a1 1 0 01-1 1h-1.587a1 1 0 01-1-1v-6a1 1 0 011-1zm-6 3h1.5a1 1 0 011 1v3a1 1 0 01-1 1h-1.5a1 1 0 01-1-1v-3a1 1 0 011-1zm6-9H16a1 1 0 011 1v1.868a1 1 0 01-1 1h-1.587a1 1 0 01-1-1V6a1 1 0 011-1zm-6 0h1.5a1 1 0 011 1v5a1 1 0 01-1 1h-1.5a1 1 0 01-1-1V6a1 1 0 011-1z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {EntityIcon};
