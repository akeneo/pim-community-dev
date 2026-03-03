import React from 'react';
import {IconProps} from './IconProps';

const SettingsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M12 15a3 3 0 100-6 3 3 0 000 6zm-9.5-5H5l.5-1.5L4 7l3-3 1.5 1.5 1.5-.47V2.5h3.953v2.53l1.547.47 2-1.5L20 6.5l-1.5 2L19 10h2.5v4H19l-.5 1.545L20 17.5 17.5 20l-2-1.5-1.547.5v2.5H10V19l-1.5-.5L7 20l-3-3 1.5-1.455L5 14H2.5v-4z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);
export {SettingsIcon};
