import React from 'react';
import {IconProps} from './IconProps';

const GiftIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g
      transform="translate(2 2)"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <rect x={1.5} y={9.5} width={17} height={10} rx={1} />
      <rect x={0.5} y={5.5} width={19} height={4} rx={1} />
      <path d="M10.133 5.665C8.43 1.135 6.927-.317 5.63 1.309c-1.617 2.026-.116 3.478 4.504 4.356z" />
      <path d="M10 5.665c1.705-4.53 3.206-5.982 4.505-4.356 1.617 2.026.115 3.478-4.505 4.356zM10 5.361v13.973" />
    </g>
  </svg>
);

export {GiftIcon};
