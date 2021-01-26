import React from 'react';
import {IconProps} from './IconProps';

const LoaderIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <circle cx="6" cy="6" r="4" stroke={color} strokeWidth="2" />
      <path stroke="#FFF" strokeLinecap="round" d="M10 6a4 4 0 00-4-4">
        <animateTransform
          attributeName="transform"
          attributeType="XML"
          type="rotate"
          dur="1s"
          from="0 6 6"
          to="360 6 6"
          repeatCount="indefinite"
        />
      </path>
    </g>
  </svg>
);

export {LoaderIcon};
