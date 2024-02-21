import React from 'react';
import {IconProps} from './IconProps';

const UserIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <path
        d="M11.9881657,13 C14.1231982,13 16.0625982,13.837602 17.4956891,15.2021291 C19.0238774,16.657204 19.9763314,18.7114561 19.9763314,20.9881657 L19.9763314,20.9881657 L4,20.9881657 C4,16.5764236 7.5764236,13 11.9881657,13 Z M12,3.00430915 C14.6470453,3.00430915 16.7928994,5.15016331 16.7928994,7.79720856 C16.7928994,10.4442538 14.6470453,12.590108 12,12.590108 C9.35295475,12.590108 7.20710059,10.4442538 7.20710059,7.79720856 C7.20710059,5.15016331 9.35295475,3.00430915 12,3.00430915 Z"
        stroke={color}
      />
    </g>
  </svg>
);

export {UserIcon};
