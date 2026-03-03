import React from 'react';
import {IconProps} from './IconProps';

const BookIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M21 7l-4.444 13.489H4.371L2.5 18 7 4h12l-4 13H5m3.5-8.5h6m-6-2h7"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {BookIcon};
