import * as React from 'react';

const Left = ({
  title = 'Left',
  color = '#67768A',
  size = 24,
  ...props
}: {title?: string; color?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M17 22L7 12 17 2" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export default Left;
