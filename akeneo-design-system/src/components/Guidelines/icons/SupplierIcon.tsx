import React from 'react';
import {IconProps} from './IconProps';

const SupplierIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M2 8.167h3v7.583H2V8.167zm17-1.084h3v8.667h-3V7.083zM5 8.708h1.861l3-2.166H14m5 1.083h-4l-2 1.083h-1l-5 3.25 1 2.167 4-2.167h2.132L19 15M5 13.75l7 4.192 6-1.109v-2.298m0 1.806c-2.333-.647-3.667-1.205-4-1.674m.984 2.733c-1.656-.63-2.65-1.18-2.984-1.65"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {SupplierIcon};
