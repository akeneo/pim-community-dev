import React from 'react';
import {IconProps} from './IconProps';

const EntityMultiIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M20 8.074V21a1 1 0 01-1 1H5a1 1 0 01-1-1V3a1 1 0 011-1h9m.413 9H16a1 1 0 011 1v6a1 1 0 01-1 1h-1.587a1 1 0 01-1-1v-6a1 1 0 011-1zm-6 3h1.5a1 1 0 011 1v3a1 1 0 01-1 1h-1.5a1 1 0 01-1-1v-3a1 1 0 011-1zm0-9h1.5a1 1 0 011 1v5a1 1 0 01-1 1h-1.5a1 1 0 01-1-1V6a1 1 0 011-1zM17 2v6m-3-3h6"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {EntityMultiIcon};
