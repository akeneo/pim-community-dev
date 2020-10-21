import React from 'react';
import {IconProps} from './IconProps';

const BrokenLinkIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M11.925 16.975l-2.95 2.95a3.5 3.5 0 01-4.95-4.95l4.95-4.95m3.05-3.05l2.95-2.95a3.5 3.5 0 014.95 4.95l-4.95 4.95m-1.163 4.836l.517 1.932M9.671 3.307l.517 1.932M16.5 16.5l2.864 2.864M4.636 4.636L7.5 7.5m11.261 6.312l1.932.517M3.307 9.671l1.932.517"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {BrokenLinkIcon};
