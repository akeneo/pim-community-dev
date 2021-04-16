import React from 'react';
import {IconProps} from './IconProps';

const LockIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} transform="translate(3 2)" fillRule="nonzero" fill="none">
      <rect x={0.5} y={7.5} width={17} height={12} rx={1} />
      <path d="M5.5 7.5V3.088C5.5 1.383 7.067 0 9 0s3.5 1.383 3.5 3.088V7.5" />
      <path d="M9 12v3" strokeLinecap="round" />
    </g>
  </svg>
);

export {LockIcon};
