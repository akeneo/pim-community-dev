import React from 'react';
import {IconProps} from './IconProps';

const BoxIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M11 7.684L22 5v14.316L11 22V7.684zm0-.184l-9-3v14l9 3.5M2 4.5l11-3m0 0L22 5M7 6l9.5-3M4 9l5 1.5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {BoxIcon};
