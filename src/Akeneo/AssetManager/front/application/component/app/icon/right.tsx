import * as React from 'react';

const Right = ({
  title = 'Right',
  color = '#67768A',
  size = 24,
  ...props
}: {title?: string; color?: string; size?: number} & any) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    <title>{title}</title>
    <path stroke={color} d="M7 2l10 10L7 22" fill="none" fillRule="evenodd" strokeLinecap="round" />
  </svg>
);

export default Right;
