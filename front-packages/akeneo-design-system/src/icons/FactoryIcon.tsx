import React from 'react';
import {IconProps} from './IconProps';

const FactoryIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M22 2v20H2V8l6 6.632V8l5 6.632V8l5.697 6.632V2z"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {FactoryIcon};
