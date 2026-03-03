import React from 'react';
import {IconProps} from './IconProps';

const EraseIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M7.384 21.479h13.979M6.091 11.314L12 17.024M7.402 21.5l-5.947-6.02L15.013 2l7.532 7.988L10.995 21.5H7.403z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {EraseIcon};
