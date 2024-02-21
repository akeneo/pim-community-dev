import React from 'react';
import {IconProps} from './IconProps';

const EntityIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M5.5 2.5h14a1 1 0 011 1v17a1 1 0 01-1 1h-14a1 1 0 01-1-1v-17a1 1 0 011-1zm9 8.5h2a1 1 0 011 1v5.5a1 1 0 01-1 1h-2a1 1 0 01-1-1V12a1 1 0 011-1zm-6 2.5h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 011-1zm6-8h2a1 1 0 011 1V8a1 1 0 01-1 1h-2a1 1 0 01-1-1V6.5a1 1 0 011-1zm-6 0h2a1 1 0 011 1v4a1 1 0 01-1 1h-2a1 1 0 01-1-1v-4a1 1 0 011-1z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);
export {EntityIcon};
