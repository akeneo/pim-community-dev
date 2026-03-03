import React from 'react';
import {IconProps} from './IconProps';

const FoldersIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M4.5 4h5.2l2.8 1.684h9v12.632M2.5 5.684H9l2.5 1.684h8V20m-9-10.947h7V20h-15V9.053m0 0V7.368h5.2l2.8 1.685"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FoldersIcon};
