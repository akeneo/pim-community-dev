import React from 'react';
import {IconProps} from './IconProps';

const ActivityIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M2 14h3.636l1.182-4.283L9.091 20l2.454-15 2.364 14 1.364-10.064L17 14h5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {ActivityIcon};
