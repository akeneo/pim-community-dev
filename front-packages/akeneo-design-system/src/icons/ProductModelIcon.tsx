import React from 'react';
import {IconProps} from './IconProps';

const ProductModelIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M8 14.25a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zm2 0a.25.25 0 11.5 0 .25.25 0 01-.5 0zM7.5 9h3v3h-3V9zm9-4h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214H4.8c-.442 0-.8-.544-.8-1.214V6.214C4 5.544 4.358 5 4.8 5h2.7m2-2h4.8v4H9.5V3zm-2 16h2m-2-2.5v2.354V16.5zm7 2.5h2m0-2.5v2.354V16.5zm-2-7.5h2m0 .5v2-2z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {ProductModelIcon};
