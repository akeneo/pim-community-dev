import React from 'react';
import {IconProps} from './IconProps';

const HelpIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width={size} height={size} fill="none" {...props}>
    {title && <title>{title}</title>}
    <path
      fill={color}
      fillRule="evenodd"
      d="M11.972 16.465a.748.748 0 1 1-.002 1.496.748.748 0 0 1 .002-1.496ZM11.986 6a3.498 3.498 0 0 1 3.486 3.487c.01 1.507-.883 2.44-1.641 3.159-.38.36-.733.684-.973 1.014-.24.33-.374.649-.374 1.076v.25a.5.5 0 0 1-.749.438.5.5 0 0 1-.247-.438v-.25c0-.658.244-1.215.568-1.661.324-.446.72-.804 1.09-1.154.74-.702 1.34-1.305 1.33-2.426a2.486 2.486 0 0 0-2.49-2.497 2.486 2.486 0 0 0-2.49 2.497.5.5 0 0 1-.749.438.5.5 0 0 1-.247-.438A3.498 3.498 0 0 1 11.986 6Z"
      clipRule="evenodd"
    />
    <path
      stroke={color}
      strokeLinecap="round"
      strokeLinejoin="round"
      d="M12 21.5a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19Z"
    />
  </svg>
);
export {HelpIcon};
