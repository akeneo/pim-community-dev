import React from 'react';
import {IconProps} from './IconProps';

const DeleteIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M5 8h14v14H5V8zm3.5 3v7.5M12 11v7.5m3.5-7.5v7.5M3 5h18v3H3V5zm5.5-2.5h7"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {DeleteIcon};
