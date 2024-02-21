import React from 'react';
import {IconProps} from './IconProps';

const AssociateIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M15 6h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214H3.3c-.442 0-.8-.544-.8-1.214V7.214c0-.67.358-1.214.8-1.214H6m2-2h4.8v4H8V4zM6.5 17.53l8-.06m-8-2.94l8-.06m-8-2.94l8-.06m2-6.97h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214M4 5.714c0-.67.358-1.214.8-1.214h2.7m2-2h4.8v2m-4.8-.616V2.5M18 3h2.7c.442 0 .8.544.8 1.214v14.572c0 .67-.358 1.214-.8 1.214M5.5 4.214c0-.67.358-1.214.8-1.214H9m2-2h4.8v2M11 2.384V1"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AssociateIcon};
