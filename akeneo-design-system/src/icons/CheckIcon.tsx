import React from 'react';
import {IconProps} from './IconProps';

const CheckIcon = ({title, size = 18, color = 'currentColor', className, ...props}: IconProps) => (
  <svg viewBox="0 0 18 18" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      className={className}
      stroke={color}
      d="M1.7 8l4.1 4 8-8"
      fill="none"
      strokeLinejoin="round"
      strokeWidth={1}
      strokeMiterlimit={10}
    />
  </svg>
);

export {CheckIcon};
