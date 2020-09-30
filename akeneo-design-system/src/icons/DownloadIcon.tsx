import React from 'react';
import {IconProps} from './IconProps';

const DownloadIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M17.11 17H20v5H4v-5h3m5-15v16V2zm5 11l-5 5.5L7 13h0"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {DownloadIcon};
