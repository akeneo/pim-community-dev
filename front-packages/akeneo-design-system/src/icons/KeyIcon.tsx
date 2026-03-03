import React from 'react';
import {IconProps} from './IconProps';

const KeyIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M7.455 9.273a1.818 1.818 0 100-3.637 1.818 1.818 0 000 3.637zm7.343 1.726a6.364 6.364 0 10-5.98 4.183h0c.856 0 1.672-.169 2.417-.475m3.674-3.616L22 18.364V22h-3.636l-1.819-1.818v-1.818h-1.818v-1.819H12.91l-1.636-1.818"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {KeyIcon};
