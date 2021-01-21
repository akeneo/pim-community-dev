import React from 'react';
import {IconProps} from './IconProps';

const CheckRoundIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g
      id="Icons-/-success"
      stroke="none"
      strokeWidth="1"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <circle id="circle" stroke={color} cx="12" cy="12" r="10"></circle>
      <polyline id="check" stroke={color} points="6.8 11.8733901 10.8528091 15.4101371 17.1067817 8.6"></polyline>
    </g>
  </svg>
);

export {CheckRoundIcon};
