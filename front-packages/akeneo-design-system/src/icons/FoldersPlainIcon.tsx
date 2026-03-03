import React from 'react';
import {IconProps} from './IconProps';

const FoldersPlainIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd">
      <path d="M2.5 7.368h5.2l2.8 1.685H17a.5.5 0 01.5.5V19.5a.5.5 0 01-.5.5H3a.5.5 0 01-.5-.5V7.368z" fill={color} />
      <path
        d="M3.5 5.684H9l2.5 1.684h8V19M4.5 4h5.2l2.8 1.684h9v12.632"
        stroke={color}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </g>
  </svg>
);

export {FoldersPlainIcon};
