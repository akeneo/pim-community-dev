import React from 'react';
import {IconProps} from './IconProps';

const ComponentIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round" stroke={color}>
      <path d="M13 3h8v8h-8zM3 3h8v8H3zM3 13h8v8H3z" />
      <circle cx={17} cy={17} r={4} />
    </g>
  </svg>
);

export {ComponentIcon};
