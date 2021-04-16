import React from 'react';
import {IconProps} from './IconProps';

const CompareIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M21 17H8.5M3 17l3 3v-6l-3 3zm0-9h12.5M21 8l-3 3V5l3 3z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {CompareIcon};
