import React from 'react';
import {IconProps} from './IconProps';

const FolderInIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M2 13h4.5m5 0l-3 3v-6l3 3zm.5-6h9v13H3v-3.47M3 9V4h5.85L12 7"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FolderInIcon};
