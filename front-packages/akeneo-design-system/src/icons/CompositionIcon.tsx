import React from 'react';
import {IconProps} from './IconProps';

const CompositionIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M18.5,12.5 L9.5,21.5 L2.5,14.5 L12,5.3 L18.5,5.5 L18.5,12.5 Z M15,10.5 C15.8284271,10.5 16.5,9.82842712 16.5,9 C16.5,8.17157288 15.8284271,7.5 15,7.5 C14.1715729,7.5 13.5,8.17157288 13.5,9 C13.5,9.82842712 14.1715729,10.5 15,10.5 Z M21.5,2.5 L16.5,7.5 M9.5,10.5 L13.5,14.5 M7.5,12.5 L11.5,16.5 M6.5,13.5 L10.5,17.5 M5.5,14.5 L9.5,18.5"
        stroke={color}
      ></path>
    </g>
  </svg>
);

export {CompositionIcon};
