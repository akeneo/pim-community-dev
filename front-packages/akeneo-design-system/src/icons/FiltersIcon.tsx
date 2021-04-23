import React from 'react';
import {IconProps} from './IconProps';

const FiltersIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M21.5 20h-10m-6 0h-3m19-7.5H19m-6 0H2.5m19-7.5H13M7 5H2.5m7 13v3.5a1 1 0 01-2 0V18a1 1 0 012 0zm7.5-7v3a1 1 0 01-2 0v-3a1 1 0 112 0zm-6-7.5v3a1 1 0 01-2 0v-3a1 1 0 012 0z"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FiltersIcon};
