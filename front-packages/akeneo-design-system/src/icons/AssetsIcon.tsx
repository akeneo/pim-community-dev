import React from 'react';
import {IconProps} from './IconProps';

const AssetsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3.5 2.5h17a1 1 0 011 1v17a1 1 0 01-1 1h-17a1 1 0 01-1-1v-17a1 1 0 011-1zm12 8.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-13 4.5l5.278-4 4.604 5 3.84-3 5.278 3"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);
export {AssetsIcon};
