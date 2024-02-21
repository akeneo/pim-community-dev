import React from 'react';
import {IconProps} from './IconProps';

const KeyboardIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M6 5h12a4 4 0 014 4v6a4 4 0 01-4 4H6a4 4 0 01-4-4V9a4 4 0 014-4zm11 7.021h2m-2-3h2m-6 3h2m-2-3h2m-6 3h2m-2-3h2m-6 3h2m-2-3h2M6.5 15.5h11"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {KeyboardIcon};
