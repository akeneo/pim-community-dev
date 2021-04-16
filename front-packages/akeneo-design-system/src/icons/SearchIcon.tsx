import React from 'react';
import {IconProps} from './IconProps';

const SearchIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g transform="translate(2 2)" fillRule="nonzero" stroke={color} fill="none">
      <path d="M12 12l7.5 7.5" strokeLinecap="round" />
      <circle cx={7} cy={7} r={7} />
    </g>
  </svg>
);

export {SearchIcon};
