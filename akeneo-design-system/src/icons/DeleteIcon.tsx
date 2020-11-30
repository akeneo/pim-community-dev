import React from 'react';
import {IconProps} from './IconProps';

const DeleteIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path className="body" d="M5 8h14v14H5zM8.5 11v7.5M12 11v7.5M15.5 11v7.5" />
      <g>
        <path className="lid" d="M3 5h18v3H3zM8.5 2.5h7" />
      </g>
    </g>
  </svg>
);

export {DeleteIcon};
