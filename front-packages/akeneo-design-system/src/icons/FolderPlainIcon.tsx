import React from 'react';
import {IconProps} from './IconProps';

const FolderPlainIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path fill={color} d="M2 4h6.5L12 6.55h10V21H2z" fillRule="evenodd" />
  </svg>
);

export {FolderPlainIcon};
