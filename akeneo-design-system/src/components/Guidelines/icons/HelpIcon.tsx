import React from 'react';
import {IconProps} from './IconProps';

const HelpIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <g transform="translate(2 2)" fill="none" fillRule="evenodd">
      <path
        d="M9.972 14.465a.748.748 0 11-.002 1.496.748.748 0 01.002-1.496zM9.986 4a3.498 3.498 0 013.486 3.487c.01 1.507-.883 2.44-1.642 3.159-.38.36-.733.684-.972 1.014-.24.33-.374.649-.374 1.077v.25a.5.5 0 01-.749.437.5.5 0 01-.247-.438v-.25c0-.658.244-1.215.568-1.661.324-.446.72-.804 1.09-1.154.74-.702 1.34-1.305 1.33-2.426a2.486 2.486 0 00-2.49-2.497 2.486 2.486 0 00-2.49 2.497.5.5 0 01-.749.438.5.5 0 01-.247-.438A3.498 3.498 0 019.986 4z"
        fill={color}
      />
      <circle stroke={color} strokeLinecap="round" strokeLinejoin="round" cx={10} cy={10} r={10} />
    </g>
  </svg>
);

export {HelpIcon};
