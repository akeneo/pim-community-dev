import React from 'react';
import {IconProps} from './IconProps';

const DangerIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M13.789 3.578l6.764 13.528A2 2 0 0118.763 20H5.237a2 2 0 01-1.789-2.894l6.764-13.528a2 2 0 013.578 0zM12 6.5v7m0 2.5v1"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {DangerIcon};
