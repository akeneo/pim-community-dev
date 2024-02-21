import React from 'react';
import {IconProps} from './IconProps';

const GroupsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        stroke={color}
        d="M19.5776804,6.5 L21.5,6.5 L21.5,21.5 L6.5,21.5 L6.5,19.6648177 M17.5,4.5 L19.5,4.5 L19.5,19.5 L4.5,19.5 L4.5,17.6087216 M2.5,2.5 L17.5,2.5 L17.5,17.5 L2.5,17.5 L2.5,2.5 Z"
      />
    </g>
  </svg>
);

export {GroupsIcon};
