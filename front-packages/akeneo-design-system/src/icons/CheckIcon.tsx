import React from 'react';
import {IconProps} from './IconProps';

const CheckIcon = ({title, size = 18, color = 'currentColor', className, ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      className={className}
      stroke={color}
      d="M2.5 12l7.5 6.5L21.5 6"
      fill="none"
      strokeLinejoin="round"
      strokeWidth={1.5}
      strokeMiterlimit={10}
    />
  </svg>
);

export {CheckIcon};
