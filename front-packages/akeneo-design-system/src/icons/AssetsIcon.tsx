import React from 'react';
import {IconProps} from './IconProps';

const AssetsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3 2h18a1 1 0 011 1v18a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1zm12.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM2 15l5.556-4 4.847 5 4.041-3L22 16"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AssetsIcon};
