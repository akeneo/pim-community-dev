import React from 'react';
import {IconProps} from './IconProps';

const FolderMovedIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M20.5 16.765V20H3v-3.235M12 7h8.5m0 0v3M3 9V4h5.85L12 7M3 9v.8V9h0m14 4h5.5M2 13h5.5m6.5 0l-3 3v-6l3 3z"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FolderMovedIcon};
