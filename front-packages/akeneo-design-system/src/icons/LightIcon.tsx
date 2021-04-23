import React from 'react';
import {IconProps} from './IconProps';

const LightIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M11.5 21h1M10 19.5h4m.5-6V17a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3.5M12 3a5.5 5.5 0 012.547 10.376M11.5 5c-2 .667-3 2-3 4m.9 4.348A5.5 5.5 0 0112 3"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {LightIcon};
