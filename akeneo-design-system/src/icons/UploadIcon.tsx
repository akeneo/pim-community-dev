import React from 'react';
import {IconProps} from './IconProps';

const UploadIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path d="M15 17h5v5H4v-5h5M12 18.5V2M7 7.5L12 2l5 5.5" />
    </g>
  </svg>
);

export {UploadIcon};
