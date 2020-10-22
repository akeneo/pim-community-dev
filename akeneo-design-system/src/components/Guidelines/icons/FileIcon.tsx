import React from 'react';
import {IconProps} from './IconProps';

const FileIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M3 2h18v20H3V2zm3.5 16h10.605M6.5 14h10.605M6.5 10h10.605M6.5 6h3"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FileIcon};
