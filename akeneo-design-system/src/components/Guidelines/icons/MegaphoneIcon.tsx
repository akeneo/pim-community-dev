import React from 'react';
import {IconProps} from './IconProps';

const MegaphoneIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <path
      d="M3.185 12.521a2.369 2.369 0 002.369 4.103l4.01-2.315 8.048-1.133a1 1 0 00.726-1.49l-4.52-7.828a1 1 0 00-1.652-.118L7.09 10.206 3.185 12.52zm4.648 2.75l2.1 3.637c.497.86.318 1.894-.4 2.308-.717.415-1.701.052-2.198-.808l-2.1-3.638h0m12.078-3.564l-5.394-9.23m5.168 5.561c.556-.355 1.046-1.123 1.046-1.831a2 2 0 00-2-2c-.42 0-.77.07-1.091.291M7.088 10.15l2.476 4.159m10.361-8.532l1.347-.695M17.229 3.63L17.77 2m2.468 7l1.663.427"
      stroke={color}
      fill="none"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </svg>
);

export {MegaphoneIcon};
