import React from 'react';
import {IconProps} from './IconProps';

const MoreIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
      <path
        d="M3.5,10.5 C4.32842712,10.5 5,11.1715729 5,12 C5,12.8284271 4.32842712,13.5 3.5,13.5 C2.67157288,13.5 2,12.8284271 2,12 C2,11.1715729 2.67157288,10.5 3.5,10.5 Z M12,10.5 C12.8284271,10.5 13.5,11.1715729 13.5,12 C13.5,12.8284271 12.8284271,13.5 12,13.5 C11.1715729,13.5 10.5,12.8284271 10.5,12 C10.5,11.1715729 11.1715729,10.5 12,10.5 Z M20.5,10.5 C21.3284271,10.5 22,11.1715729 22,12 C22,12.8284271 21.3284271,13.5 20.5,13.5 C19.6715729,13.5 19,12.8284271 19,12 C19,11.1715729 19.6715729,10.5 20.5,10.5 Z"
        fill={color}
      />
    </g>
  </svg>
);

export {MoreIcon};
