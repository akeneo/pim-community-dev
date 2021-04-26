import React from 'react';
import {IconProps} from './IconProps';

const CreditsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M12 5v8.063m-5.013 5.872C5.268 18.711 4 17.93 4 17V5m0 10c0 .933 1.278 1.717 3.006 1.938M4 13c0 .938 1.292 1.725 3.035 1.941M4 11c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M4 9c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M4 7c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2M8 7c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2zm6.973 10.933c.328.044.672.067 1.027.067 2.21 0 4-.895 4-2v-6m-5.032 5.933c.33.044.675.067 1.032.067 2.21 0 4-.895 4-2m0-2c0 1.105-1.79 2-4 2-.513 0-1.268-.095-1.718-.183m-1.77-.696C12.06 12.85 12 12.36 12 12v-2m4 2c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2zm-1 3v4c0 1.105-1.79 2-4 2s-4-.895-4-2v-4m0 2c0 1.105 1.79 2 4 2h0c2.21 0 4-.895 4-2m-4 0c2.21 0 4-.895 4-2s-1.79-2-4-2-4 .895-4 2 1.79 2 4 2z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {CreditsIcon};
