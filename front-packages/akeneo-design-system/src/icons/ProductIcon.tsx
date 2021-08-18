import React from 'react';
import {IconProps} from './IconProps';

const ProductIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M16.03 4.5h3.47v17h-15v-17h3.552m1.448-2h5v4h-5v-4zM8 16.53l8-.06m-8-2.94l8-.06m-8-2.94l8-.06"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);
export {ProductIcon};
