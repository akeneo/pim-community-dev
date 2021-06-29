import React from 'react';
import {IconProps} from './IconProps';

const ArrowLeftIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      vectorEffect="non-scaling-stroke"
      d="M17 22L7 12 17 2"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {ArrowLeftIcon};
