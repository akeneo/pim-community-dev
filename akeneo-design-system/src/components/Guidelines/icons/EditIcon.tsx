import React from 'react';
import {IconProps} from './IconProps';

const EditIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke={color} fill="none" fillRule="evenodd" strokeLinecap="round">
      <path d="M6.5 6h3M6.5 14.5h1M14.984 14.5h2.126M6.5 18h10.605" />
      <path strokeLinejoin="round" d="M9.32 10.368l8.606-7.749 3.011 3.344-8.606 7.75-3.315.045z" />
      <path d="M11 10.5l1.237 1.445" />
      <path strokeLinejoin="round" d="M21 9.3V22H3V2h11" />
    </g>
  </svg>
);

export {EditIcon};
