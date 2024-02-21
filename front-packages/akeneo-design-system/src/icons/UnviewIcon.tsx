import React from 'react';
import {IconProps} from './IconProps';

const UnviewIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M12 18c3.682 0 7.015-2 10-6-2.985-4-6.318-6-10-6-3.682 0-7.015 2-10 6 2.985 4 6.318 6 10 6zm0-2a4 4 0 100-8 4 4 0 000 8zm.707-4.707a1 1 0 00-1.414 1.414h0M22 2L2 22"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {UnviewIcon};
