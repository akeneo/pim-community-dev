import React from 'react';
import {IconProps} from './IconProps';

const PanelCloseIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M7 17.749V21H2V3h5v3.375M22 12H6h16zm-11 5l-5.5-5L11 7h0"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PanelCloseIcon};
