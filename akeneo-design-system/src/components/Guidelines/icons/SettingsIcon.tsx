import React from 'react';
import {IconProps} from './IconProps';

const SettingsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M11.5 14.5a3 3 0 100-6 3 3 0 000 6zM2 9.5h2.5L5 8 3.5 6.5l3-3L8 5l1.5-.47V2h3.953v2.53L15 5l2-1.5L19.5 6 18 8l.5 1.5H21v4h-2.5l-.5 1.545L19.5 17 17 19.5 15 18l-1.547.5V21H9.5v-2.5L8 18l-1.5 1.5-3-3L5 15.045 4.5 13.5H2v-4z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {SettingsIcon};
