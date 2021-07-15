import React from 'react';
import {IconProps} from './IconProps';

const TableIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M4,5 L20,5 C20.5522847,5 21,5.44771525 21,6 L21,18 C21,18.5522847 20.5522847,19 20,19 L4,19 C3.44771525,19 3,18.5522847 3,18 L3,6 C3,5.44771525 3.44771525,5 4,5 Z M3,10 L21,10 M3,13 L21,13 M3,16 L21,16 M9,5 L9,19 M15,5 L15,19"
        stroke={color}
      ></path>
    </g>
  </svg>
);

export {TableIcon};
