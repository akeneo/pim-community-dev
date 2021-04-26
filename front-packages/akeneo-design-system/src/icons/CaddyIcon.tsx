import React from 'react';
import {IconProps} from './IconProps';

const CaddyIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <path d="M5 18a2 2 0 110 4 2 2 0 010-4zm15 0a2 2 0 110 4 2 2 0 010-4z" fill={color} />
      <path
        d="M1 4.522h2.5m0 0l2 9.978M22.5 4l-2 10.5m-15 0h14.98m-14.887-.065L4.086 16.5v.5H21"
        stroke={color}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </g>
  </svg>
);

export {CaddyIcon};
