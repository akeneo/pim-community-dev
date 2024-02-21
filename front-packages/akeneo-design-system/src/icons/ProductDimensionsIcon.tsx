import React from 'react';
import {IconProps} from './IconProps';

const ProductDimensionsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M20.5,3.5 L20.5,16.5 M16.5,20.5 L3.5,20.5 M19.0857864,4.5 L20.5,3.08578644 L21.9142136,4.5 M21.9142136,15.5 L20.5,16.9142136 L19.0857864,15.5 M4.79289322,21.9142136 L3.37867966,20.5 L4.79289322,19.0857864 M15.7928932,19.0857864 L17.2071068,20.5 L15.7928932,21.9142136 M8,3 L12,3 L14,12 L6,12 L8,3 Z M10,12.5 L10,17.5 M6.5,17.5 L13.5,17.5"
        stroke={color}
      ></path>
    </g>
  </svg>
);

export {ProductDimensionsIcon};
