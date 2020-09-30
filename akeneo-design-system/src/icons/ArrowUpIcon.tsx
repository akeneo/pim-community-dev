import React from 'react';
import {IconProps} from './IconProps';

const ArrowUpIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path stroke={color} d="M2 17L12 7l10 10h0" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {ArrowUpIcon};
