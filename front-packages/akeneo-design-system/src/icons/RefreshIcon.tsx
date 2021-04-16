import React from 'react';
import {IconProps} from './IconProps';

const RefreshIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M7.886 4C4.962 5.609 3 8.575 3 11.97c0 3.437 2.013 6.436 5 8.03m2-.5l3 2.5v-5l-3 2.5zM15.114 4C18.038 5.609 20 8.575 20 11.97c0 3.437-2.013 6.436-5 8.03M13 4.5L10 7V2l3 2.5z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {RefreshIcon};
