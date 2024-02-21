import React from 'react';
import {IconProps} from './IconProps';

const DateIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M2 8h20v13a1 1 0 01-1 1H3a1 1 0 01-1-1V8h0zm1 0h18a1 1 0 001-1V5a1 1 0 00-1-1H3a1 1 0 00-1 1v2a1 1 0 001 1zm14.5-5.5v4m-5.5-4v4m-5.5-4v4m11 9.5a1 1 0 110 2 1 1 0 010-2zM12 16a1 1 0 110 2 1 1 0 010-2zm-5.5 0a1 1 0 110 2 1 1 0 010-2zm11-4a1 1 0 110 2 1 1 0 010-2zM12 12a1 1 0 110 2 1 1 0 010-2zm-5.5 0a1 1 0 110 2 1 1 0 010-2z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {DateIcon};
