import React from 'react';
import {IconProps} from './IconProps';

const CodeIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M8 6.5l-5.589 4.879a.5.5 0 00-.017.737L8 17.5h0m8-11l5.589 4.879a.5.5 0 01.017.737L16 17.5h0m-6 2.469L14 4.03"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
    />
  </svg>
);

export {CodeIcon};
