import React from 'react';
import {IconProps} from './IconProps';

const FullscreenIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round" stroke={color}>
      <path d="M9 21H3v-6m.5 5.5L9 15m6-12h6v6m-5.5-.5l5-5M21 15v6h-6m5.5-.5L15 15M3 9V3h6m-.5 5.5l-5-5" />
    </g>
  </svg>
);

export {FullscreenIcon};
