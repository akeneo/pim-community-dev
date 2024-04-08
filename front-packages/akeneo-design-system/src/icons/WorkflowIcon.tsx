import React from 'react';
import {IconProps} from './IconProps';

const WorkflowIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M17.1379 5.49999H8V4.49999H17.1379C19.2709 4.49999 21 6.29085 21 8.49999C21 10.7091 19.2709 12.5 17.1379 12.5H14V11.5H17.1379C18.7377 11.5 20.0345 10.1568 20.0345 8.49999C20.0345 6.84313 18.7377 5.49999 17.1379 5.49999Z"
      fill={color}
    />
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M5.88571 18H19.3408L19.83 18.5L19.3408 19H5.88571C3.73969 19 2 17.7091 2 15.5C2 13.2908 3.73969 11.5 5.88571 11.5H8V12.5H5.88571C4.2762 12.5 2.97143 13.8431 2.97143 15.5C2.97143 17.1568 4.2762 18 5.88571 18Z"
      fill={color}
    />
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M7 3H3L3 7H7V3ZM3 2C2.44772 2 2 2.44772 2 3V7C2 7.55228 2.44772 8 3 8H7C7.55228 8 8 7.55228 8 7V3C8 2.44772 7.55228 2 7 2H3Z"
      fill={color}
    />
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M13 10.2H9V13.8H13V10.2ZM9 9C8.44772 9 8 9.53726 8 10.2V13.8C8 14.4627 8.44772 15 9 15H13C13.5523 15 14 14.4627 14 13.8V10.2C14 9.53726 13.5523 9 13 9H9Z"
      fill={color}
    />
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M20.8731 18.1966C21.0423 18.3695 21.0423 18.6305 20.8731 18.8034L18.8731 20.8476C18.6893 21.0355 18.3733 21.0519 18.1672 20.8843C17.9611 20.7168 17.9431 20.4286 18.1269 20.2407L19.83 18.5L18.1269 16.7593C17.9431 16.5714 17.9611 16.2832 18.1672 16.1157C18.3733 15.9481 18.6893 15.9645 18.8731 16.1524L20.8731 18.1966Z"
      fill={color}
    />
  </svg>
);

export {WorkflowIcon};
