import React from 'react';
import {IconProps} from './IconProps';

const MediaLinkIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M22 13v8a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1h8M2 15l5.556-4 4.847 5 4.041-3L22 16M16 2h6v6m0-6l-9 9"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {MediaLinkIcon};
