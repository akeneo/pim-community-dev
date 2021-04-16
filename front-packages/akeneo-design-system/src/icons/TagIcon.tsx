import React from 'react';
import {IconProps} from './IconProps';

const TagIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M18.5 12.5l-9 9-7-7L12 5.3l6.5.2v7zm-3.5-2a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm6.5-8l-5 5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {TagIcon};
