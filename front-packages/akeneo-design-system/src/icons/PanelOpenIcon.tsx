import React from 'react';
import {IconProps} from './IconProps';

const PanelOpenIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M16 17.749V21h5V3h-5v3.375M2 12h15H2zm10 5l5.5-5L12 7h0"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PanelOpenIcon};
