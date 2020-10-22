import React from 'react';
import {IconProps} from './IconProps';

const ColumnIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path d="M2.5 2.5V22m9-19.5V22m10-19.5V22" stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {ColumnIcon};
