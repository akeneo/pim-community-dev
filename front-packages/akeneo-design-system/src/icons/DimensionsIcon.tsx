import React from 'react';
import {IconProps} from './IconProps';

const DimensionsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M9.33333333,7.12280702 L16.6666667,5.33333333 L16.6666667,14.877193 L9.33333333,16.6666667 L9.33333333,7.12280702 Z M9.33333333,7 L3.33333333,5 L3.33333333,14.3333333 L9.33333333,16.6666667 M3.33333333,5 L10.6666667,3 M10.6666667,3 L16.6666667,5.33333333 M6.66666667,6 L13,4 M4.66666667,8 L8,9 M20.5,3.5 L20.5,16.5 M16.5,20.5 L3.5,20.5 M19.0857864,4.5 L20.5,3.08578644 L21.9142136,4.5 M21.9142136,15.5 L20.5,16.9142136 L19.0857864,15.5 M4.79289322,21.9142136 L3.37867966,20.5 L4.79289322,19.0857864 M15.7928932,19.0857864 L17.2071068,20.5 L15.7928932,21.9142136"
        stroke={color}
      ></path>
    </g>
  </svg>
);

export {DimensionsIcon};
