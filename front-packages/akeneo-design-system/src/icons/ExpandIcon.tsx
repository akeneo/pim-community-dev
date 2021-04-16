import React from 'react';
import {IconProps} from './IconProps';

const ExpandIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M10 21H3v-7m.5 6.5L10 14m4-11h7v7m-6.5-.5l6-6"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {ExpandIcon};
