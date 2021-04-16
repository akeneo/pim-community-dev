import React from 'react';
import {IconProps} from './IconProps';

const WaveIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3 16c-.305-5.333 1.028-8 4-8 2.256 0 4.972.571 4.972 4 0 3.429 1.798 4 4.028 4 4.514 0 5-2.857 5-8"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {WaveIcon};
