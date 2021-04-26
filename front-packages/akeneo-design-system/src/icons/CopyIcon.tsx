import React from 'react';
import {IconProps} from './IconProps';

const CopyIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path d="M17 7h5v15H7v-5M2 2h15v15H2V2z" stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export {CopyIcon};
