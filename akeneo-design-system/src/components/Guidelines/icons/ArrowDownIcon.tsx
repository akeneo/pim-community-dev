import React from 'react';
import {IconProps} from './IconProps';

const ArrowDownIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path stroke={color} d="M2 7l10 10L22 7" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {ArrowDownIcon};
