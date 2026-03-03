import React from 'react';
import {IconProps} from './IconProps';

const UploadIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 18.5V2M7 7.5L12 2l5 5.5m-1.813 9H20.5v5h-17v-5h5.313" />
    </g>
  </svg>
);
export {UploadIcon};
