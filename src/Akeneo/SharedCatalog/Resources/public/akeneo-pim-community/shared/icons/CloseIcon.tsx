import React, {SVGProps} from 'react';
import {useAkeneoTheme} from '../hooks/useAkeneoTheme';

type IconProps = {
  color?: string;
  title?: string;
  size?: number;
} & SVGProps<SVGSVGElement>;

const CloseIcon = ({title = 'Close', color, size = 24, ...props}: IconProps) => (
  <svg viewBox='0 0 24 24' width={size} height={size} {...props}>
    <g fillRule='nonzero' stroke={color || useAkeneoTheme().color.grey100} fill='none' strokeLinecap='round'>
      <path d='M4 4l16 16M20 4L4 20' />
    </g>
    <title>{title}</title>
  </svg>
);

export {CloseIcon};
