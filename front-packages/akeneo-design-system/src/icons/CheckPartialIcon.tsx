import React from 'react';
import {IconProps} from './IconProps';

const CheckPartialIcon = ({title, size = 24, color = 'currentColor', className, ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      className={className}
      d="M2 12.5h20"
      stroke={color}
      strokeWidth={2}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {CheckPartialIcon};
