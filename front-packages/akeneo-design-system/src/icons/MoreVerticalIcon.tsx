import React from 'react';
import {IconProps} from './IconProps';

const MoreVerticalIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M10.5 20.5a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm1.5-10a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM12 2a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"
      fill={color}
      fillRule="evenodd"
    />
  </svg>
);

export {MoreVerticalIcon};
