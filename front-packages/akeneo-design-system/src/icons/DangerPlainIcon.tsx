import React from 'react';
import {IconProps} from './IconProps';

const DangerPlainIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M12.894 2.683a2 2 0 01.895.895l6.764 13.528A2 2 0 0118.763 20H5.237a2 2 0 01-1.789-2.894l6.764-13.528a2 2 0 012.683-.895zM12 16a1 1 0 100 2 1 1 0 000-2zm0-10a.5.5 0 00-.5.5v7a.5.5 0 001 0v-7A.5.5 0 0012 6z"
      fill={color}
      fillRule="evenodd"
    />
  </svg>
);

export {DangerPlainIcon};
