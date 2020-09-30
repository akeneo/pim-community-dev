import React from 'react';
import {IconProps} from './IconProps';

const MoreIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3.5 10.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm8.5 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm8.5 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"
      fill={color}
      fillRule="evenodd"
    />
  </svg>
);

export {MoreIcon};
