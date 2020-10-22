import React from 'react';
import {IconProps} from './IconProps';

const SystemIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M21 15a1 1 0 010 2h-3a1 1 0 010-2h3zM6 13.5a1 1 0 010 2H3a1 1 0 010-2h3zm7.5-6a1 1 0 010 2h-3a1 1 0 110-2h3zm6 11.5v3m0-19v10M12 11.5V22m0-19v2.5m-7.5 12V22m0-19v8.5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {SystemIcon};
