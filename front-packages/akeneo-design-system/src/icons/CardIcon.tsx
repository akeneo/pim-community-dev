import React from 'react';
import {IconProps} from './IconProps';

const CardIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M10.5 13.5v8h-8v-8h8zm11 0v8h-8v-8h8zm-11-11v8h-8v-8h8zm11 0v8h-8v-8h8z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);
export {CardIcon};
