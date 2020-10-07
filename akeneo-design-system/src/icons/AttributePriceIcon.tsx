import React from 'react';
import {IconProps} from './IconProps';

const AttributePriceIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M15.5 6.542C14.5 5.514 13.333 5 12 5c-2 0-3.5 1-3.5 3 0 4 7 3.466 7 7.208 0 2.334-2.5 5.792-8 2.192m4-15.4v3m0 14v2.5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AttributePriceIcon};
