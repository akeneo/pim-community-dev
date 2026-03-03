import React from 'react';
import {IconProps} from './IconProps';

const NotificationIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M9 18c0 1.988 1.112 3 3.1 3s2.9-1.012 2.9-3m4.75-1.163c.45-1.462-.825-1.782-1.35-3.146-.478-1.241-.273-2.698-.273-3.491 0-3.976-2.05-6.75-6.027-6.75-3.976 0-5.85 2.774-5.85 6.75 0 .954.115 2.29-.45 3.6-.525 1.219-1.8 1.39-1.35 3.037.23.84 3.825.654 7.65.654s7.357.298 7.65-.654z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {NotificationIcon};
