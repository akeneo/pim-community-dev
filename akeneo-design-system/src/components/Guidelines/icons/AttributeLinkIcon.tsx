import React from 'react';
import {IconProps} from './IconProps';

const AttributeLinkIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M11.925 16.975l-2.95 2.95a3.5 3.5 0 01-4.95-4.95l4.95-4.95a3.5 3.5 0 015.941 1.99m-2.89-5.04l2.949-2.95a3.5 3.5 0 014.95 4.95l-4.95 4.95a3.5 3.5 0 01-5.935-1.943"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AttributeLinkIcon};
