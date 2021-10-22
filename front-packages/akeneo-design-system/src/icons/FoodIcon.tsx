import React from 'react';
import {IconProps} from './IconProps';

const FoodIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M8,13 C8,11.8954305 7.1045695,11 6,11 C4.8954305,11 4,11.8954305 4,13 M21,13 C21,10.790861 19.209139,9 17,9 C16.6441363,9 16.2991266,9.04647111 15.9707082,9.13367587 M8,16 C8,18.209139 9.790861,20 12,20 C14.209139,20 16,18.209139 16,16 M2,13 L22,13 L22,22 L2,22 L2,13 Z M8,6 L11,6 L11,13 L8,13 L8,6 Z M9.5,4 L11,6 L8,6 L9.5,4 Z M14.5,4 L16,6 L14.5,4 Z M9.5,2 L14.5,2 L14.5,4 L9.5,4 L9.5,2 Z M11,6 L16,6 L16,13 L11,13 L11,6 Z M6.5,15.5 L9.5,15.5 M14.5,15.5 L17.5,15.5"
        stroke={color}
      ></path>
    </g>
  </svg>
);

export {FoodIcon};
