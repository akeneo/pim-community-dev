import React from 'react';
import {IconProps} from './IconProps';

const UnpublishIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M21 9.3V22H3V2h11M6.5 18h10.605M6.5 14h10.605M6.5 6h3m7 4.5h-4m0 0v-4m0 4l8-8"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {UnpublishIcon};
