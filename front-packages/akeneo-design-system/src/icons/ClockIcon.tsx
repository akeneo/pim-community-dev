import React from 'react';
import {IconProps} from './IconProps';

const ClockIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <path
        d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-9.47V6.673m0 5.859l3.557-1.475L12 12.53z"
        stroke={color}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M12 19.5a.5.5 0 110 1 .5.5 0 010-1zm-5.657-2.55a.5.5 0 110 1 .5.5 0 010-1zm11.595 0a.5.5 0 110 1 .5.5 0 010-1zM20 11.5a.5.5 0 110 1 .5.5 0 010-1zm-16 0a.5.5 0 110 1 .5.5 0 010-1zm13.657-5.864a.5.5 0 110 1 .5.5 0 010-1zm-11.032 0a.5.5 0 110 1 .5.5 0 010-1zM12 3.5a.5.5 0 110 1 .5.5 0 010-1z"
        fill={color}
      />
    </g>
  </svg>
);

export {ClockIcon};
