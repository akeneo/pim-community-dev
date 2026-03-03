import React from 'react';
import {IconProps} from './IconProps';

const MinifyIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3 14h7v7m-6.5-.5L10 14m11-4h-7V3m.5 6.5l6-6"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {MinifyIcon};
