import React from 'react';
import {IconProps} from './IconProps';

const AttributeBooleanIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M7.5 6h9a6.5 6.5 0 110 13h-9a6.5 6.5 0 110-13zm0 10a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AttributeBooleanIcon};
