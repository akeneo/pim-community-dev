import React from 'react';
import {IconProps} from './IconProps';

const ProductIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M16.5 5h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214H4.8c-.442 0-.8-.544-.8-1.214V6.214C4 5.544 4.358 5 4.8 5h2.7m2-2h4.8v4H9.5V3zM8 16.53l8-.06m-8-2.94l8-.06m-8-2.94l8-.06"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {ProductIcon};
