import React from 'react';
import {IconProps} from './IconProps';

const ExternalLinkIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M16.047 3.02258H20.9561L20.9561 7.93171M20.9561 3.02258L12.2914 11.6874L20.9561 3.02258Z"
      stroke={color}
      strokeLinecap="round"
      strokeLinejoin="round"
      fill="none"
    />
    <path d="M21 12.1088V21H3V3H12.0679" stroke={color} strokeLinecap="round" strokeLinejoin="round" fill="none" />
  </svg>
);

export {ExternalLinkIcon};
