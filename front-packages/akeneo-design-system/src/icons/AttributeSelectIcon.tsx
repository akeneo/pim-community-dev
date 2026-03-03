import React from 'react';
import {IconProps} from './IconProps';

const AttributeSelectIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3 2h18a1 1 0 011 1v18a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1zm6 6l.63 7.87 2.203-1.889 1.89 2.519 1.258-.944-1.888-2.393 2.518-1.07L9 8z"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {AttributeSelectIcon};
