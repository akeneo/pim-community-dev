import React from 'react';
import {IconProps} from './IconProps';

const GroupsIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M19 7h3v15H7v-2M17 5h2v15H4v-3M2 2h15v15H2V2z"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {GroupsIcon};
