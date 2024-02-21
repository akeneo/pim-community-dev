import React from 'react';
import {IconProps} from './IconProps';

const PanelCloseIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M14,21 L5,12 L14,3 M19,21 L10,12 L19,3"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PanelCloseIcon};
