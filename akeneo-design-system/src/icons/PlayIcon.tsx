import React from 'react';
import {IconProps} from './IconProps';

const PlayIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M19.422 13.553L5.894 20.317A2 2 0 013 18.527V5a2 2 0 012.894-1.789l13.528 6.764a2 2 0 010 3.578z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {PlayIcon};
