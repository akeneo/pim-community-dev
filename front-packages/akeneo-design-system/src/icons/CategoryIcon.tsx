import React from 'react';
import {IconProps} from './IconProps';

const CategoryIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M9.842 14.372h4.3m0-6.064h-4.3v11.787h4.3M5.5 6v6.709h4M16 18.19V22h5v-2.857h-2l-1-.953h-2zm0-5.714v3.81h5v-2.857h-2l-1-.953h-2zm0-5.714v3.81h5V7.713h-2l-1-.952h-2zM3 2v3.81h5V2.952H6L5 2H3z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {CategoryIcon};
