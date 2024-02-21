import React from 'react';
import {IconProps} from './IconProps';

const LinkIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      stroke={color}
      d="M8.442 11.347c-.535.535-1.42.517-1.977-.04L2.43 7.272c-.557-.557-.575-1.442-.04-1.977L5.295 2.39c.535-.535 1.42-.517 1.977.04l4.035 4.035c.557.557.575 1.442.04 1.977m3.632 3.632c.535-.535 1.42-.517 1.977.04h0l4.035 4.035c.557.557.575 1.443.04 1.977l-2.905 2.906c-.534.534-1.42.516-1.977-.04l-4.035-4.036c-.557-.557-.575-1.442-.04-1.977m-3.39-6.295l6.053 6.053"
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {LinkIcon};
