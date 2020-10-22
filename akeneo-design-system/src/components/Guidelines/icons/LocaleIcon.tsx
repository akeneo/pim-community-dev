import React from 'react';
import {IconProps} from './IconProps';

const LocaleIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M2.5 4v18M9 5V2h13v12h-6M9.5 2.5l6 2.5m-13 0h13v12h-13V5z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {LocaleIcon};
