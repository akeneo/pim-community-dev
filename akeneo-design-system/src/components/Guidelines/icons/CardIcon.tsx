import React from 'react';
import {IconProps} from './IconProps';

const CardIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M11 13v9H2v-9h9zm11 0v9h-9v-9h9zM11 2v9H2V2h9zm11 0v9h-9V2h9z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {CardIcon};
