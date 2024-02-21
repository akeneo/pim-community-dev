import React from 'react';
import {IconProps} from './IconProps';

const PanelOpenIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M10,21 L19,12 L10,3 M5,21 L14,12 L5,3"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PanelOpenIcon};
