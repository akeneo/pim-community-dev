import React from 'react';
import {IconProps} from './IconProps';

const DialogIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M8 16H3.9c-.497 0-.9-.416-.9-.929V3.93C3 3.416 3.403 3 3.9 3h16.2c.497 0 .9.416.9.929V15.07c0 .513-.403.929-.9.929H18m-7.12-1.368V20H12l3.6-5.368"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {DialogIcon};
