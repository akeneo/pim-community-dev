import React from 'react';
import {IconProps} from './IconProps';

const FolderOutIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M12 13h4.5m5 0l-3 3v-6l3 3zm-1 4.39V20H3V8m9-1h8.5m0 0v2M3 9V4h5.85L12 7"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FolderOutIcon};
