import React from 'react';
import {IconProps} from './IconProps';

const ArrowRightIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      vectorEffect="non-scaling-stroke"
      d="M7 2l10 10L7 22"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {ArrowRightIcon};
