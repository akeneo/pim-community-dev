import React from 'react';
import {IconProps} from './IconProps';

const AssetCollectionIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3 4h16a1 1 0 011 1v16a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1zm11 8a2 2 0 100-4 2 2 0 000 4zM1.889 15.778l5-3.555 4.361 4.444 3.638-2.666 5 2.666M5 2h17.111v17.5"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AssetCollectionIcon};
