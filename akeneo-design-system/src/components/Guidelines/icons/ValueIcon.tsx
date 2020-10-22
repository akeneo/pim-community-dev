import React from 'react';
import {IconProps} from './IconProps';

const ValueIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M2.5 14.5L12 5.3M9.5 21.5l9-9M12 5.3l6.5.2m0 6.998V5.5v6.998zM2.5 14.5l7 7m12-19l-5 5m-1.5 3a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm-8.5 3l3.89 3.89M8 12l3 3m-1.5-4.5l3.89 3.89"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {ValueIcon};
